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
    public function testProcess()
    {
        $productTransformer = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $productTransformer
            ->expects($this->once())
            ->method('getTransformedColumnsInfo')
            ->will($this->returnValue(array()));
        $productTransformer
            ->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $processor = new ProductProcessor(
            $this->validator,
            $this->translator,
            $productTransformer
        );

        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $processor->setStepExecution($stepExecution);

        $processor->setEnabled('enabled');
        $processor->setFamilyColumn('fml');
        $processor->setCategoriesColumn('ctg');
        $processor->setGroupsColumn('grp');

        $data = array('key' => 'val1', 'fml' => 'val2', 'ctg' => 'val3', 'grp' => 'val4');
        $mappedData = array(
            'key'           => 'val1',
            'family'        => 'val2',
            'categories'    => 'val3',
            'groups'        => 'val4'
        );
        $entity = new \stdClass();
        $productTransformer->expects($this->once())
            ->method('transform')
            ->with(
                $this->equalTo($mappedData),
                $this->equalTo(
                    array(
                        'enabled' => 'enabled'
                    )
                )
            )
            ->will($this->returnValue($entity));

        $this->assertSame($entity, $processor->process($data));
    }
}
