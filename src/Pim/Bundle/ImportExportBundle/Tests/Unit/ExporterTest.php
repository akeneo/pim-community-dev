<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit;

use Pim\Bundle\ImportExportBundle\Exporter;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExporterTest extends \PHPUnit_Framework_TestCase
{
    public function testExport()
    {
        $serializer = $this
            ->getMockBuilder('Symfony\Component\Serializer\Serializer')
            ->setConstructorArgs(array(array(), array(new XmlEncoder)))
            ->getMock();
        $reader     = $this->getMocked('Pim\Bundle\ImportExportBundle\Reader\DoctrineReader');
        $writer     = $this->getMocked('Pim\Bundle\ImportExportBundle\Writer\FilePutContentsWriter');
        $exporter   = new Exporter($serializer, $reader, $writer, 'xml');

        $reader->expects($this->once())
            ->method('read')
            ->will($this->returnValue(array('foo', 'bar')));

        $serializer->expects($this->any())
            ->method('supportsEncoding')
            ->will($this->returnValue(true));

        $writer->expects($this->once())
            ->method('write')
            ->with(<<<XML
<?xml version="1.0"?>
<response><item key="0">foo</item><item key="1">bar</item></response>

XML
            );

        $exporter->export();
    }

    public function getMocked($class)
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

