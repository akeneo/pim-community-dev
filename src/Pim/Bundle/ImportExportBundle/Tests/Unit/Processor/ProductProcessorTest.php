<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ProductProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorTest extends TransformerProcessorTestCase
{
    protected $transformer;
    protected $processor;

    protected function setUp()
    {
        parent::setUp();
        $this->processor = new ProductProcessor(
            $this->validator,
            $this->translator,
            $this->transformer,
            'product_class'
        );
    }

    public function testProcess()
    {
        $this->transformer
            ->expects($this->once())
            ->method('getTransformedColumnsInfo')
            ->will($this->returnValue(array()));
        $this->transformer
            ->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor->setStepExecution($stepExecution);

        $this->processor->setEnabled('enabled');
        $this->assertEquals('enabled', $this->processor->isEnabled());
        $this->processor->setFamilyColumn('fml');
        $this->assertEquals('fml', $this->processor->getFamilyColumn());
        $this->processor->setCategoriesColumn('ctg');
        $this->assertEquals('ctg', $this->processor->getCategoriesColumn());
        $this->processor->setGroupsColumn('grp');
        $this->assertEquals('grp', $this->processor->getGroupsColumn());

        $data = array('key' => 'val1', 'fml' => 'val2', 'ctg' => 'val3', 'grp' => 'val4');
        $mappedData = array(
            'key'           => 'val1',
            'family'        => 'val2',
            'categories'    => 'val3',
            'groups'        => 'val4'
        );
        $entity = new \stdClass();
        $this->transformer->expects($this->once())
            ->method('transform')
            ->with(
                $this->equalTo('product_class'),
                $this->equalTo($mappedData),
                $this->equalTo(
                    array(
                        'enabled' => 'enabled'
                    )
                )
            )
            ->will($this->returnValue($entity));

        $this->assertSame($entity, $this->processor->process($data));
    }

    public function testGetConfigurationFields()
    {
        $this->assertInternalType('array', $this->processor->getConfigurationFields());
    }
}
