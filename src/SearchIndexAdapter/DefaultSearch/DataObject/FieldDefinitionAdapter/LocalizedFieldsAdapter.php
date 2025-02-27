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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\DataObject\FieldDefinitionAdapter;

use Exception;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\LanguageServiceInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Localizedfield;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @internal
 */
final class LocalizedFieldsAdapter extends AbstractAdapter
{
    private LanguageServiceInterface $languageService;

    public function getIndexMapping(): array
    {
        $mapping = [
            'properties' => [],
        ];
        $languages = $this->languageService->getValidLanguages();
        /** @var Data\Localizedfields $fieldDefinition */
        $fieldDefinition = $this->getFieldDefinition();
        $childFieldDefinitions = $fieldDefinition->getFieldDefinitions();

        foreach ($languages as $language) {
            $languageProperties = [];

            foreach ($childFieldDefinitions as $childFieldDefinition) {
                $fieldDefinitionAdapter = $this->getFieldDefinitionService()->getFieldDefinitionAdapter(
                    $childFieldDefinition
                );
                if ($fieldDefinitionAdapter) {
                    $mappingKey = $fieldDefinitionAdapter->getIndexAttributeName();

                    $languageProperties[$mappingKey] = $fieldDefinitionAdapter->getIndexMapping();
                }
            }

            $mapping['properties'][$language] = [
                'properties' => $languageProperties,
            ];
        }

        return $mapping;
    }

    /**
     * @param mixed $value
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function normalize(mixed $value): ?array
    {
        if (!$value instanceof Localizedfield) {
            return null;
        }

        $value->loadLazyData();

        /** @var Data\Localizedfields $fieldDefinition */
        $fieldDefinition = $this->getFieldDefinition();

        $attributes = [];
        $indexData = $fieldDefinition->normalize($value);

        $languages = array_keys($indexData);
        if (!empty($indexData)) {
            $attributes = array_keys(reset($indexData));
        }

        $result = [];
        foreach ($attributes as $attribute) {
            foreach ($languages as $language) {
                $localizedValue = $value->getLocalizedValue($attribute, $language);
                $fieldDefinition = $value->getFieldDefinition($attribute);
                $localizedValue =  $this->fieldDefinitionService->normalizeValue($fieldDefinition, $localizedValue);
                $result[$attribute][$language] = $localizedValue;
            }
        }

        return $result;
    }

    #[Required]
    public function setLanguageService(LanguageServiceInterface $languageService): void
    {
        $this->languageService = $languageService;
    }
}
