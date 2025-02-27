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

namespace Pimcore\Bundle\GenericDataIndexBundle\Tests\Functional\Transformer\SearchResultItem;

use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Document\SearchResult\DocumentSearchResultItem;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Search\SearchService\Document\DocumentSearchServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Transformer\SearchResultItem\DocumentToSearchResultItemTransformerInterface;
use Pimcore\Tests\Support\Util\TestHelper;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentToSearchResultItemTransformerTest extends \Codeception\Test\Unit
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

    public function testTransform()
    {
        /**
         * @var DocumentToSearchResultItemTransformerInterface $transformer
         */
        $transformer = $this->tester->grabService(DocumentToSearchResultItemTransformerInterface::class);

        // create asset
        $object = TestHelper::createEmptyDocument();

        $folder = TestHelper::createDocumentFolder();
        $object
            ->setParent($folder)
            ->save();

        $this->assetItemEqualsIndexItem($transformer->transform($object));
        $folderItem = $transformer->transform($folder);
        $this->assetItemEqualsIndexItem($folderItem);
        $this->assertTrue($folderItem->isHasChildren());
    }

    private function assetItemEqualsIndexItem(DocumentSearchResultItem $item)
    {

        /**
         * @var SerializerInterface $serializer
         */
        $serializer = $this->tester->grabService(SerializerInterface::class);

        /**
         * @var DocumentSearchServiceInterface $searchService
         */
        $searchService = $this->tester->grabService(DocumentSearchServiceInterface::class);

        $this->assertEquals($serializer->normalize($searchService->byId($item->getId(), null, true)), $serializer->normalize($item));
    }
}
