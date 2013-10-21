<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Pim\Bundle\ImportExportBundle\Writer\ProductFileWriter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFileWriterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mediaManager = $this->getMediaManagerMock();
        $this->writer = new ProductFileWriter($this->mediaManager);
        $this->writer->setStepExecution($this->getStepExecutionMock());
    }

    public function testIsAnInstanceOfFileWriter()
    {
        $this->assertInstanceOf('Pim\Bundle\ImportExportBundle\Writer\FileWriter', $this->writer);
    }

    public function testWrite()
    {
        $media1 = $this->getMediaMock();
        $media2 = $this->getMediaMock();
        $media3 = $this->getMediaMock();
        $media4 = $this->getMediaMock();
        $media5 = $this->getMediaMock();

        $this->mediaManager
            ->expects($this->at(0))
            ->method('copy')
            ->with($media1, '/tmp/phpunit/');

        $this->mediaManager
            ->expects($this->at(1))
            ->method('copy')
            ->with($media2, '/tmp/phpunit/');

        $this->mediaManager
            ->expects($this->at(2))
            ->method('copy')
            ->with($media3, '/tmp/phpunit/');

        $this->mediaManager
            ->expects($this->at(3))
            ->method('copy')
            ->with($media3, '/tmp/phpunit/');

        $this->mediaManager
            ->expects($this->at(4))
            ->method('copy')
            ->with($media3, '/tmp/phpunit/');

        $this
            ->writer
            ->setDirectoryName('/tmp/phpunit/')
            ->write(
                array(
                    array(
                        'entry' => 'foo',
                        'media' => array($media1, $media2, $media3),
                    ),
                    array(
                        'entry' => 'bar',
                        'media' => array($media4, $media5),
                    )
                )
            );
    }

    protected function getMediaMock()
    {
        return $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Media');
    }

    protected function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
