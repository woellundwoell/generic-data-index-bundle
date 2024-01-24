<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under following license:
 * - Pimcore Commercial License (PCL)
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\IndexService\IndexHandler;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\DataObject\FieldDefinitionService;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\IndexService\ElementTypeAdapter\DataObjectTypeAdapter;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\SearchIndexConfigService;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Contracts\Service\Attribute\Required;

class DataObjectIndexHandler extends AbstractIndexHandler
{
    private FieldDefinitionService $fieldDefinitionService;

    private DataObjectTypeAdapter $dataObjectTypeAdapter;

    public function extractMappingProperties(mixed $context = null): array
    {
        if (!$context instanceof ClassDefinition) {
            return [];
        }

        return $this->extractMappingByClassDefinition(
            $context
        );
    }

    protected function getAliasIndexName(mixed $context = null): string
    {
        if ($context instanceof ClassDefinition) {
            return $this->searchIndexConfigService->getIndexName($context->getName());
        }

        return $this->searchIndexConfigService->getIndexName('data_object_folders');
    }

    private function extractMappingByClassDefinition(ClassDefinition $classDefinition): array
    {
        $mappingProperties = [
            FieldCategory::SYSTEM_FIELDS->value => [
                'properties' => $this->searchIndexConfigService
                    ->getSystemFieldsSettings(SearchIndexConfigService::SYSTEM_FIELDS_SETTINGS_DATA_OBJECT),
            ],
            FieldCategory::STANDARD_FIELDS->value => [
                'properties' => [],
            ],
            FieldCategory::CUSTOM_FIELDS->value => [],
        ];

        foreach ($classDefinition->getFieldDefinitions() as $fieldDefinition) {
            if (!$fieldDefinition->getName()) {
                continue;
            }
            $fieldDefinitionAdapter = $this->fieldDefinitionService->getFieldDefinitionAdapter($fieldDefinition);
            if ($fieldDefinitionAdapter) {
                $mappingProperties[FieldCategory::STANDARD_FIELDS->value]['properties'][$fieldDefinitionAdapter->getOpenSearchAttributeName()] = $fieldDefinitionAdapter->getOpenSearchMapping();
            }
        }

        //$extractMappingEvent = new ExtractMappingEvent($classDefinition, $mappingProperties[FieldCategory::CUSTOM_FIELDS->value]);
        //$this->eventDispatcher->dispatch($extractMappingEvent);
        //$mappingProperties[FieldCategory::CUSTOM_FIELDS->value]['properties'] = $extractMappingEvent->getCustomFieldsMapping();

        return $mappingProperties;
    }

    #[Required]
    public function setFieldDefinitionService(FieldDefinitionService $fieldDefinitionService): void
    {
        $this->fieldDefinitionService = $fieldDefinitionService;
    }

    #[Required]
    public function setDataObjectTypeAdapter(DataObjectTypeAdapter $dataObjectTypeAdapter): void
    {
        $this->dataObjectTypeAdapter = $dataObjectTypeAdapter;
    }
}
