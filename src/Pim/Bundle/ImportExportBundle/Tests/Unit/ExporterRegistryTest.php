<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit;

use Pim\Bundle\ImportExportBundle\ExporterRegistry;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExporterRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->registry = new ExporterRegistry;
    }

    public function testRegisterExporter()
    {
        $exporter = $this->getExporterMock();
        $this->registry->registerExporter('foo', $exporter);

        $this->assertEquals(array(
            'foo' => $exporter,
        ), $this->registry->getExporters());
    }

    public function testGetExporter()
    {
        $exporter = $this->getExporterMock();
        $this->registry->registerExporter('foo', $exporter);

        $this->assertEquals($exporter, $this->registry->getExporter('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUndefinedExporter()
    {
        $exporter = $this->getExporterMock();
        $this->registry->getExporter('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterAlreadyDefinedExporter()
    {
        $exporter = $this->getExporterMock();
        $this->registry->registerExporter('foo', $exporter);
        $this->registry->registerExporter('foo', $exporter);
    }

    private function getExporterMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Exporter')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

