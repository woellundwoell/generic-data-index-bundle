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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\Search\Modifier\FullTextSearch;

use Pimcore\Bundle\GenericDataIndexBundle\Attribute\Search\AsSearchModifierHandler;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\WildcardFilterMode;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory\SystemField;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Modifier\SearchModifierContextInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\SimpleQueryStringFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\WildcardFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\ElementKeySearch;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\FullTextSearch;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\WildcardSearch;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Search\SearchService\SearchPqlFieldNameTransformationServiceInterface;

/**
 * @internal
 */
final readonly class FullTextSearchHandlers
{
    public function __construct(
        private SearchPqlFieldNameTransformationServiceInterface $fieldNameTransformationService,
    ) {
    }

    #[AsSearchModifierHandler]
    public function handleElementKeySearch(
        ElementKeySearch $elementKeySearch,
        SearchModifierContextInterface $context
    ): void {
        if (empty($elementKeySearch->getSearchTerm())) {
            return;
        }

        $context->getSearch()
            ->addQuery(
                new WildcardFilter(
                    SystemField::KEY->getPath(),
                    $elementKeySearch->getSearchTerm(),
                    WildcardFilterMode::SUFFIX
                )
            );
    }

    #[AsSearchModifierHandler]
    public function handleWildcardSearch(
        WildcardSearch $wildcardSearch,
        SearchModifierContextInterface $context
    ): void {
        if (empty($wildcardSearch->getSearchTerm())) {
            return;
        }

        $fieldName = $wildcardSearch->getFieldName();
        if ($wildcardSearch->isPqlFieldNameResolutionEnabled()) {
            $fieldName = $this->fieldNameTransformationService->transformFieldnameForSearch(
                $context->getOriginalSearch(),
                $fieldName
            );
        }

        $context->getSearch()
            ->addQuery(
                new WildcardFilter(
                    $fieldName,
                    $wildcardSearch->getSearchTerm(),
                    WildcardFilterMode::BOTH
                )
            );
    }

    #[AsSearchModifierHandler]
    public function handleMultiMatchSearch(
        FullTextSearch $fullValueSearch,
        SearchModifierContextInterface $context
    ): void {
        if (empty($fullValueSearch->getSearchTerm())) {
            return;
        }

        $context->getSearch()->addQuery(new SimpleQueryStringFilter($fullValueSearch->getSearchTerm()));
    }
}
