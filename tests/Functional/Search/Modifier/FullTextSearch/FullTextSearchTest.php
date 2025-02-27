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

namespace Pimcore\Bundle\GenericDataIndexBundle\Tests\Functional\Search\Modifier\FullTextSearch;

use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\ElementKeySearch;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\FullTextSearch;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Modifier\FullTextSearch\WildcardSearch;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Search\SearchService\Asset\AssetSearchServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Search\SearchService\SearchProviderInterface;
use Pimcore\Tests\Support\Util\TestHelper;

final class FullTextSearchTest extends \Codeception\Test\Unit
{
    /**
     * @var \Pimcore\Bundle\GenericDataIndexBundle\Tests\IndexTester
     */
    protected $tester;

    protected function _before()
    {
        $this->tester->enableSynchronousProcessing();
    }

    protected function _after()
    {
        TestHelper::cleanUp();
        $this->tester->flushIndex();
        $this->tester->cleanupIndex();
        $this->tester->flushIndex();
    }

    // tests
    public function testElementKeySearch(): void
    {
        $asset = TestHelper::createImageAsset();
        $asset->setFilename('Test image 123.jpg')->save();

        /** @var AssetSearchServiceInterface $searchService */
        $searchService = $this->tester->grabService('generic-data-index.test.service.asset-search-service');
        /** @var SearchProviderInterface $searchProvider */
        $searchProvider = $this->tester->grabService(SearchProviderInterface::class);

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('Test'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('Test*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('*123.jpg'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('*123*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('*123'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());
        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('123*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());

        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());
        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new ElementKeySearch('123'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());

    }

    public function testWildcardSearch(): void
    {
        $asset = TestHelper::createImageAsset();
        $asset->setFilename('Test image 123.jpg')->save();

        /** @var AssetSearchServiceInterface $searchService */
        $searchService = $this->tester->grabService('generic-data-index.test.service.asset-search-service');
        /** @var SearchProviderInterface $searchProvider */
        $searchProvider = $this->tester->grabService(SearchProviderInterface::class);

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '*/Test*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullpath', '*/Test*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('filename', 'Test*'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '/Test'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '*123.jpg'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '*123.jp'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '*123.jp?'))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('system_fields.fullPath', '123', false))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([$asset->getId()], $searchResult->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new WildcardSearch('fullPath', '123', false))
        ;
        $searchResult = $searchService->search($assetSearch);
        $this->assertEquals([], $searchResult->getIds());
    }

    public function testFullTextSearch(): void
    {
        $asset = TestHelper::createImageAsset();
        $asset->setFilename('Asset 1.jpg')->setKey('123')->save();
        $asset2 = TestHelper::createImageAsset();
        $asset2->setFilename('Asset 2.jpg')->setKey('456')->save();

        /** @var AssetSearchServiceInterface $searchService */
        $searchService = $this->tester->grabService('generic-data-index.test.service.asset-search-service');
        /** @var SearchProviderInterface $searchProvider */
        $searchProvider = $this->tester->grabService(SearchProviderInterface::class);

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new FullTextSearch($asset->getKey()))
        ;
        $this->assertEquals([$asset->getId()], $searchService->search($assetSearch)->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new FullTextSearch($asset2->getKey()))
        ;
        $this->assertEquals([$asset2->getId()], $searchService->search($assetSearch)->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new FullTextSearch('asset'))
        ;
        $this->assertEquals([$asset->getId(), $asset2->getId()], $searchService->search($assetSearch)->getIds());

        $assetSearch = $searchProvider
            ->createAssetSearch()
            ->addModifier(new FullTextSearch($asset2->getFilename()))
        ;
        $this->assertEquals([$asset2->getId()], $searchService->search($assetSearch)->getIds());
    }
}
