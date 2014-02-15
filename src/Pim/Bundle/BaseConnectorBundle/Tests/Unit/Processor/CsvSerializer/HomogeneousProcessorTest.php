<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Processor\CsvSerializer;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HomogeneousProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HomogeneousProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->serializer = $this
            ->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager
            ->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue(['fr', 'de', 'it']));

        $this->processor = new HomogeneousProcessor($this->serializer, $this->manager);
    }

    /**
     * Test related method
     */
    public function testProcess()
    {
        $stepExecution = $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor->setStepExecution($stepExecution);
        $this->processor->setDelimiter(';');
        $this->processor->setEnclosure('"');
        $this->processor->setWithHeader(true);
        $item = new \StdClass();

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
                    'locales'       => ['fr', 'de', 'it'],
                )
            )
            ->will($this->returnValue('serialized'));

        $this->assertEquals('serialized', $this->processor->process($item));
    }
}
