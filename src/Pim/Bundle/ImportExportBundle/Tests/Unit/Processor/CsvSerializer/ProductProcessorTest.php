<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor\CsvSerializer;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\ImportExportBundle\Processor\CsvSerializer\ProductProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->serializer     = $this->getSerializerMock();
        $this->channelManager = $this->getChannelManagerMock();
        $this->localeManager  = $this->getLocaleManagerMock();
        $this->processor      = new ProductProcessor($this->serializer, $this->localeManager, $this->channelManager);
    }

    /**
     * Test related method
     */
    public function testInstanceOfHeterogeneousProcessor()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\ImportExportBundle\Processor\CsvSerializer\HeterogeneousProcessor',
            $this->processor
        );
    }

    /**
     * Test related method
     */
    public function testStoresMediaAmongWithSerializedProducts()
    {
        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor->setStepExecution($stepExecution);
        $this->processor->setDelimiter(';');
        $this->processor->setEnclosure('"');
        $this->processor->setWithHeader(true);

        $media1 = $this->getMediaMock();
        $media2 = $this->getMediaMock();
        $media3 = $this->getMediaMock();
        $media4 = $this->getMediaMock();
        $media5 = $this->getMediaMock();
        $media6 = $this->getMediaMock();

        $board = $this->getProductMock(array($media1, $media2));
        $sail  = $this->getProductMock(array($media3));
        $mast  = $this->getProductMock(array($media4, $media5, $media6));

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue('serialized'));

        $this->assertEquals(
            array(
                'entry' => 'serialized',
                'media' => array($media1, $media2, $media3, $media4, $media5, $media6)
            ),
            $this->processor->process(array($board, $sail, $mast))
        );
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSerializerMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMediaMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getChannelManagerMock()
    {
        $channel = new Channel();
        $channel->setCode('mobile');

        $mock = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getChannelByCode')
            ->will($this->returnValue($channel));

        return $mock;
    }

    public function getLocaleManagerMock()
    {
        return $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $media
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductMock(array $media)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getMedia')
            ->will($this->returnValue($media));

        return $product;
    }
}
