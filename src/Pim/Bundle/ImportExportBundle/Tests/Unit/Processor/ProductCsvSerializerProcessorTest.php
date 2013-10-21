<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ProductCsvSerializerProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvSerializerProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->serializer   = $this->getSerializerMock();
        $this->processor    = new ProductCsvSerializerProcessor($this->serializer);
    }

    public function testInstanceOfHeterogeneousCsvSerializerProcessor()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\ImportExportBundle\Processor\HeterogeneousCsvSerializerProcessor',
            $this->processor
        );
    }

    public function testStoresMediaAmongWithSerializedProducts()
    {
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

        $context = array(
            'delimiter'     => ';',
            'enclosure'     => '"',
            'withHeader'    => true,
            'heterogeneous' => true,
        );

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
        return $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Media');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductMock(array $media)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');

        $product->expects($this->any())
            ->method('getMedia')
            ->will($this->returnValue($media));

        return $product;
    }
}
