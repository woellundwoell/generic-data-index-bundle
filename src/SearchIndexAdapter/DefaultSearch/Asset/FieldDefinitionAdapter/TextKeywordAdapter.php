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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\Asset\FieldDefinitionAdapter;

use Pimcore\Bundle\GenericDataIndexBundle\Exception\InvalidArgumentException;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\WildcardFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Interfaces\AdapterSearchInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\Aggregation\Asset\AssetMetaDataAggregation;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\Filter\Asset\AssetMetaDataFilter;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\IndexMappingServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\SearchIndexConfigServiceInterface;

/**
 * @internal
 */
final class TextKeywordAdapter extends AbstractAdapter
{
    public function __construct(
        protected SearchIndexConfigServiceInterface $searchIndexConfigService,
        private readonly IndexMappingServiceInterface $indexMappingService,
    ) {
        parent::__construct(
            $searchIndexConfigService
        );
    }

    public function getIndexMapping(): array
    {
        return $this->indexMappingService->getMappingForTextKeyword(
            $this->searchIndexConfigService->getSearchAnalyzerAttributes()
        );
    }

    public function applySearchFilter(AssetMetaDataFilter $filter, AdapterSearchInterface $adapterSearch): void
    {
        if ($filter->getType() !== $this->getType()) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s does not support filter type "%s" for filter "%s"',
                    self::class,
                    $filter->getType(),
                    $filter->getName()
                )
            );
        }

        $searchTerm = $filter->getData();
        if (!is_string($searchTerm)) {
            throw new InvalidArgumentException('Search term must be a string');
        }

        if (empty($searchTerm)) {
            return;
        }

        $adapterSearch
            ->addQuery(
                new WildcardFilter(
                    $this->getSearchFilterFieldPath($filter),
                    $searchTerm
                )
            );
    }

    protected function getSearchFilterFieldPath(AssetMetaDataFilter|AssetMetaDataAggregation $filter): string
    {
        return parent::getSearchFilterFieldPath($filter) . '.keyword';
    }
}
