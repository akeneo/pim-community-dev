<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\HomogeneousCsvSerializerProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HomogeneousCsvSerializerProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->serializer = $this
            ->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new HomogeneousCsvSerializerProcessor($this->serializer);
    }

    public function testProcess()
    {
        $this->processor->setDelimiter(';');
        $this->processor->setEnclosure('"');
        $this->processor->setWithHeader(true);
        $item = new \StdClass;

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $item,
                'csv',
                array(
                    'delimiter'     => ';',
                    'enclosure'     => '"',
                    'withHeader'    => true,
                    'heterogeneous' => false,
                )
            )
            ->will($this->returnValue('serialized'));

        $this->assertEquals('serialized', $this->processor->process($item));
    }
}
