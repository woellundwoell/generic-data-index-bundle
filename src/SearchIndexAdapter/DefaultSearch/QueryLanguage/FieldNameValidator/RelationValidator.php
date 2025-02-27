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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\QueryLanguage\FieldNameValidator;

use Pimcore\Bundle\GenericDataIndexBundle\Model\SearchIndex\IndexEntity;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\MappingAnalyzerServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\QueryLanguage\FieldNameValidatorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @internal
 */
#[AsTaggedItem(priority: 2)]
final readonly class RelationValidator implements FieldNameValidatorInterface
{
    public function __construct(
        private MappingAnalyzerServiceInterface $mappingAnalyzerService
    ) {
    }

    public function validateFieldName(
        string $originalFieldName,
        string $fieldName,
        array $indexMapping,
        ?IndexEntity $targetEntity = null
    ): ?string {
        if ($targetEntity) {
            $isValidRelationField = false;
            foreach (['assets', 'asset', 'object', 'document'] as $type) {

                if (str_ends_with($fieldName, '.' . $type)) {
                    $isValidRelationField = true;

                    break;
                }

                $relationField = $fieldName . '.' . $type;
                if ($this->mappingAnalyzerService->fieldPathExists($relationField, $indexMapping)) {
                    $isValidRelationField = true;

                    break;
                }
            }

            if (!$isValidRelationField) {
                return sprintf(
                    'Field `%s` is not a valid relation field.',
                    $originalFieldName
                );
            }
        }

        return null;
    }
}
