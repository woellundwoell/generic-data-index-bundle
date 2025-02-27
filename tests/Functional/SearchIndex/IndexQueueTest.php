<?php

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

namespace Functional\SearchIndex;

use Codeception\Test\Unit;
use Exception;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\ElementType;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\IndexName;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\IndexQueueOperation;
use Pimcore\Bundle\GenericDataIndexBundle\Repository\IndexQueueRepository;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\SearchIndexConfigServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Tests\IndexTester;
use Pimcore\Db;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Tests\Support\Util\TestHelper;

class IndexQueueTest extends Unit
{
    protected IndexTester $tester;

    private SearchIndexConfigServiceInterface $searchIndexConfigService;

    private const ASSET_INDEX_NAME = 'asset';

    private const DOCUMENT_INDEX_NAME = 'document';

    protected function _before()
    {
        $this->searchIndexConfigService = $this->tester->grabService(
            SearchIndexConfigServiceInterface::class
        );
        $this->tester->disableSynchronousProcessing();
        $this->tester->clearQueue();
    }

    protected function _after()
    {
        TestHelper::cleanUp();
        $this->tester->flushIndex();
        $this->tester->cleanupIndex();
        $this->tester->flushIndex();
    }

    // tests

    public function testIndexQueueRepository(): void
    {
        /**
         * @var IndexQueueRepository $indexQueueRepository
         */
        $indexQueueRepository = $this->tester->grabService(IndexQueueRepository::class);

        $entries = $indexQueueRepository->getUnhandledIndexQueueEntries();
        $entries = array_map(fn ($entry) => $indexQueueRepository->denormalizeDatabaseEntry($entry), $entries);
        $indexQueueRepository->deleteQueueEntries($entries);

        TestHelper::createImageAsset();

        $this->assertEquals(1, $indexQueueRepository->countIndexQueueEntries());
        $this->assertTrue($indexQueueRepository->dispatchableItemExists());

        $this->assertCount(1, $indexQueueRepository->getUnhandledIndexQueueEntries());
        // check if not dispatched
        $this->assertCount(1, $indexQueueRepository->getUnhandledIndexQueueEntries());

        $dispatchedItems = $indexQueueRepository->getUnhandledIndexQueueEntries(true);
        usleep(1000); //sleep for 1 ms to ensure that the dispatchId is different
        $this->assertEquals([], $indexQueueRepository->getUnhandledIndexQueueEntries(true));

        $dispatchedItems = array_map(fn ($entry) => $indexQueueRepository->denormalizeDatabaseEntry($entry), $dispatchedItems);

        $this->assertEquals(1, $indexQueueRepository->countIndexQueueEntries());
        $indexQueueRepository->deleteQueueEntries($dispatchedItems);
        $this->assertEquals(0, $indexQueueRepository->countIndexQueueEntries());

        $indexQueueRepository->enqueueBySelectQuery(
            $indexQueueRepository->generateSelectQuery('assets', [
                ElementType::ASSET->value,
                IndexName::ASSET->value,
                IndexQueueOperation::UPDATE->value,
                1234,
                0,
            ])
        );
        $this->assertEquals(
            Db::get()->fetchOne('select count(id) from assets'),
            $indexQueueRepository->countIndexQueueEntries()
        );
    }

    public function testAssetSaveNotEnqueued(): void
    {
        $indexName = $this->searchIndexConfigService->getIndexName(self::ASSET_INDEX_NAME);

        $asset = TestHelper::createImageAsset();
        $this->tester->checkDeletedIndexEntry($asset->getId(), $indexName);
    }

    public function testAssetSaveProcessQueue(): void
    {
        /**
         * @var SearchIndexConfigServiceInterface $searchIndexConfigService
         */
        $searchIndexConfigService = $this->tester->grabService(SearchIndexConfigServiceInterface::class);
        $indexName = $searchIndexConfigService->getIndexName(self::ASSET_INDEX_NAME);

        $asset = TestHelper::createImageAsset();

        $this->assertGreaterThan(
            0,
            Db::get()->fetchOne(
                'select count(elementId) from generic_data_index_queue where elementId = ? and elementType="asset"',
                [$asset->getId()]
            )
        );

        $this->consume();
        $result = $this->tester->checkIndexEntry($asset->getId(), $indexName);
        $this->assertEquals($asset->getId(), $result['_source']['system_fields']['id']);
    }

    /**
     * @throws Exception
     */
    public function testAssetDeleteWithQueue(): void
    {
        $asset = TestHelper::createImageAsset();
        $assetIndex = $this->searchIndexConfigService->getIndexName(self::ASSET_INDEX_NAME);
        $this->consume();

        $this->checkAndDeleteElement($asset, $assetIndex);
        $this->consume();

        $this->tester->checkDeletedIndexEntry($asset->getId(), $assetIndex);
    }

    /**
     * @throws Exception
     */
    public function testDocumentDeleteWithQueue(): void
    {
        $document = TestHelper::createEmptyDocument();
        $documentIndex = $this->searchIndexConfigService->getIndexName(self::DOCUMENT_INDEX_NAME);
        $this->consume();

        $this->checkAndDeleteElement($document, $documentIndex);
        $this->consume();

        $this->tester->checkDeletedIndexEntry($document->getId(), $documentIndex);
    }

    /**
     * @throws Exception
     */
    public function testDataObjectDeleteWithQueue(): void
    {
        $object = TestHelper::createEmptyObject();
        $objectIndex = $this->searchIndexConfigService->getIndexName($object->getClassName());
        $this->consume();

        $this->checkAndDeleteElement($object, $objectIndex);
        $this->consume();

        $this->tester->checkDeletedIndexEntry($object->getId(), $objectIndex);
    }

    private function checkAndDeleteElement(ElementInterface $element, string $indexName): void
    {
        $this->tester->checkIndexEntry($element->getId(), $indexName);
        $element->delete();
    }

    private function consume(): void
    {
        $this->tester->runCommand('messenger:consume', ['--limit'=>2], ['pimcore_generic_data_index_queue']);
    }
}
