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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\Search\Modifier\Aggregation;

use Pimcore\Bundle\GenericDataIndexBundle\Attribute\Search\AsSearchModifierHandler;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory\SystemField;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Aggregation\Aggregation;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Modifier\SearchModifierContextInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\TermsFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\Aggregation\Tree\ChildrenCountAggregation;

/**
 * @internal
 */
final class TreeAggregations
{
    #[AsSearchModifierHandler]
    public function handleChildrenCountAggregation(
        ChildrenCountAggregation $aggregation,
        SearchModifierContextInterface $context
    ): void {
        $context->getSearch()
            ->addQuery(
                new TermsFilter(
                    field: SystemField::PARENT_ID->getPath(),
                    terms: $aggregation->getParentIds(),
                )
            )
            ->addAggregation(
                new Aggregation(
                    name: $aggregation->getAggregationName(),
                    params: [
                        'terms' => [
                            'field' => SystemField::PARENT_ID->getPath(),
                            'size' => count($aggregation->getParentIds()),
                        ],
                    ]
                )
            )
            ->setSize(0)
        ;
    }
}
