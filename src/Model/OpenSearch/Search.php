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

namespace Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch;

use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Sort\FieldSort;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Sort\FieldSortList;
use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Aggregation\Aggregation;
use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Aggregation\AggregationList;
use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Query\QueryInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Query\QueryList;

/**
 * @deprecated Will be removed in 2.0, please use
 * Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Search instead
 */
final class Search implements OpenSearchSearchInterface
{
    public function __construct(
        private ?int $from = null,
        private ?int $size = null,
        private array|bool|string|null $source = null,
        private readonly QueryList $queryList = new QueryList(),
        private readonly AggregationList $aggregationList = new AggregationList(),
        private FieldSortList $sortList = new FieldSortList(),
        private bool $reverseItemOrder = false,
        private ?array $searchAfter = null,

    ) {
    }

    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function setFrom(?int $from): OpenSearchSearchInterface
    {
        $this->from = $from;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): OpenSearchSearchInterface
    {
        $this->size = $size;

        return $this;
    }

    public function getSource(): bool|array|string|null
    {
        return $this->source;
    }

    public function setSource(bool|array|string|null $source): OpenSearchSearchInterface
    {
        $this->source = $source;

        return $this;
    }

    public function addQuery(QueryInterface $query = null): OpenSearchSearchInterface
    {
        $this->queryList->addQuery($query);

        return $this;
    }

    public function addSort(FieldSort $sort): OpenSearchSearchInterface
    {
        $this->sortList->addSort($sort);

        return $this;
    }

    public function addAggregation(Aggregation $aggregation): OpenSearchSearchInterface
    {
        $this->aggregationList->addAggregation($aggregation);

        return $this;
    }

    public function getQueryList(): QueryList
    {
        return $this->queryList;
    }

    public function getSortList(): FieldSortList
    {
        return $this->sortList;
    }

    public function setSortList(FieldSortList $sortList): OpenSearchSearchInterface
    {
        $this->sortList = $sortList;

        return $this;
    }

    public function isReverseItemOrder(): bool
    {
        return $this->reverseItemOrder;
    }

    public function setReverseItemOrder(bool $reverseItemOrder): OpenSearchSearchInterface
    {
        $this->reverseItemOrder = $reverseItemOrder;

        return $this;
    }

    public function getSearchAfter(): ?array
    {
        return $this->searchAfter;
    }

    public function setSearchAfter(?array $searchAfter): OpenSearchSearchInterface
    {
        $this->searchAfter = is_array($searchAfter) && !empty($searchAfter) ? $searchAfter : null;

        return $this;
    }

    public function toArray(): array
    {
        $result = [
            'from' => $this->getSearchAfter() !== null ? null : $this->from,
            'size' => $this->size,
            '_source' => $this->source,
        ];

        foreach ($result as $key => $value) {
            if ($value === null) {
                unset($result[$key]);
            }
        }

        if (!$this->queryList->isEmpty()) {
            $result['query'] = $this->queryList->toArray();
        }

        if (!$this->aggregationList->isEmpty()) {
            $result['aggs'] = $this->aggregationList->toArray();
        }

        if (!$this->sortList->isEmpty()) {
            $result['sort'] = $this->sortList->toArray();
        }

        if ($this->getSearchAfter() !== null) {
            $result['search_after'] = $this->getSearchAfter();
        }

        return $result;
    }
}
