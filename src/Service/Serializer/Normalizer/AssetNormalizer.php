<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\Service\Serializer\Normalizer;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\ElementType;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory\SystemField;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\SerializerContext;
use Pimcore\Bundle\GenericDataIndexBundle\Model\SearchIndexAdapter\MappingProperty;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\Asset\FieldDefinitionServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Dependency\DependencyServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\Asset\MetadataProviderServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Serializer\AssetTypeSerializationHandlerService;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Workflow\WorkflowServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Traits\ElementNormalizerTrait;
use Pimcore\Model\Asset;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class AssetNormalizer implements NormalizerInterface
{
    use ElementNormalizerTrait;

    public function __construct(
        private readonly AssetTypeSerializationHandlerService $assetTypeSerializationHandlerService,
        private readonly FieldDefinitionServiceInterface $fieldDefinitionService,
        private readonly WorkflowServiceInterface $workflowService,
        private readonly MetadataProviderServiceInterface $metadataProviderService,
        private readonly DependencyServiceInterface $dependencyService,
    ) {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $skipLazyLoadedFields = SerializerContext::SKIP_LAZY_LOADED_FIELDS->containedInContext($context);

        $asset = $object;

        if ($asset instanceof Asset\Folder) {
            return $this->normalizeFolder($asset, $skipLazyLoadedFields);
        }

        if ($asset instanceof Asset) {
            return $this->normalizeAsset($asset, $skipLazyLoadedFields);
        }

        return [];
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof Asset;
    }

    private function normalizeFolder(Asset\Folder $folder, bool $skipLazyLoadedFields): array
    {
        return [
            FieldCategory::SYSTEM_FIELDS->value => $this->normalizeSystemFields($folder, $skipLazyLoadedFields),
            FieldCategory::STANDARD_FIELDS->value => [],
        ];
    }

    private function normalizeAsset(Asset $asset, bool $skipLazyLoadedFields): array
    {
        return [
            FieldCategory::SYSTEM_FIELDS->value => $this->normalizeSystemFields($asset, $skipLazyLoadedFields),
            FieldCategory::STANDARD_FIELDS->value => $this->normalizeStandardFields($asset),
        ];
    }

    private function normalizeSystemFields(Asset $asset, bool $skipLazyLoadedFields): array
    {
        $systemFields = [
            SystemField::ID->value => $asset->getId(),
            SystemField::ELEMENT_TYPE->value => ElementType::ASSET->value,
            SystemField::PARENT_ID->value => $asset->getParentId(),
            SystemField::CREATION_DATE->value => $this->formatTimestamp($asset->getCreationDate()),
            SystemField::MODIFICATION_DATE->value => $this->formatTimestamp($asset->getModificationDate()),
            SystemField::TYPE->value => $asset->getType(),
            SystemField::KEY->value => $asset->getKey(),
            SystemField::FULL_PATH->value => $asset->getRealFullPath(),
            SystemField::PATH->value => $asset->getPath(),
            SystemField::MIME_TYPE->value => $asset->getMimeType(),
            SystemField::USER_OWNER->value => $asset->getUserOwner(),
            SystemField::USER_MODIFICATION->value => $asset->getUserModification(),
            SystemField::LOCKED->value => $asset->getLocked(),
            SystemField::IS_LOCKED->value => $asset->isLocked(),
        ];

        if (!$skipLazyLoadedFields) {
            $pathLevels = $this->extractPathLevels($asset);

            $systemFields = array_merge($systemFields, [
                SystemField::FILE_SIZE->value => $asset->getFileSize(),
                SystemField::DEPENDENCIES->value => $this->dependencyService->getRequiresDependencies($asset),
                SystemField::HAS_WORKFLOW_WITH_PERMISSIONS->value =>
                    $this->workflowService->hasWorkflowWithPermissions($asset),
                SystemField::PATH_LEVELS->value => $pathLevels,
                SystemField::PATH_LEVEL->value => count($pathLevels),
                SystemField::TAGS->value => $this->extractTagIds($asset),
                SystemField::PARENT_TAGS->value => $this->extractParentTagIds($asset),
            ]);
        }

        if ($handler = $this->assetTypeSerializationHandlerService->getSerializationHandler($asset->getType())) {
            $systemFields = array_merge($systemFields, $handler->getAdditionalSystemFields($asset));
        }

        return $systemFields;
    }

    private function normalizeStandardFields(Asset $asset): array
    {
        $result = [];

        foreach ($this->metadataProviderService->getSearchableMetaDataForAsset($asset) as $metadata) {
            $data = $metadata['data'];
            $language = $metadata['language'] ?? null;
            $language = $language ?: MappingProperty::NOT_LOCALIZED_KEY;
            $result[$metadata['name']] = $result[$metadata['name']] ?? [];
            $result[$metadata['name']][$language] = $this->fieldDefinitionService->normalizeValue(
                $metadata['type'],
                $data
            );
        }

        return $result;
    }
}
