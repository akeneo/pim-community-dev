<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\NestedFamilyProcessor;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedFamilyProcessorTest extends AbstractTransformerProcessorTestCase
{
    protected $processor;
    protected $transformer;
    protected $data = array(
        'key' => 'val',
        'requirements' => array(
            'channel1' => array(
                'attribute1',
                'attribute2'
            ),
            'channel2' => array(
                'attribute3'
            ),
        )
    );
    protected $entity;

    protected function setUp()
    {
        parent::setUp();
        $this->transformer = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor = new NestedFamilyProcessor(
            $this->validator,
            $this->translator,
            $this->transformer,
            'family_class',
            'requirements_class'
        );

        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor->setStepExecution($stepExecution);

        $this->entity = new \stdClass;
    }

    public function testTransform()
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');
        $this->transformer
            ->expects($this->at(0))
            ->method('transform')
            ->with($this->equalTo('family_class'), $this->equalTo(array('key' => 'val')))
            ->will($this->returnValue($family));
        $this->transformer
            ->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));
        $this->transformer
            ->expects($this->at(2))
            ->method('transform')
            ->with(
                $this->equalTo('requirements_class'),
                $this->equalTo($this->getRequirementData('channel1', 'attribute1'))
            )
            ->will($this->returnValue('requirement1'));
        $this->transformer
            ->expects($this->at(4))
            ->method('transform')
            ->with(
                $this->equalTo('requirements_class'),
                $this->equalTo($this->getRequirementData('channel1', 'attribute2'))
            )
            ->will($this->returnValue('requirement2'));
        $this->transformer
            ->expects($this->at(7))
            ->method('transform')
            ->with(
                $this->equalTo('requirements_class'),
                $this->equalTo($this->getRequirementData('channel2', 'attribute3'))
            )
            ->will($this->returnValue('requirement3'));
        $this->transformer
            ->expects($this->never())
            ->method('getTransformedColumnsInfo');
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnArgument(4));
        $family->expects($this->once())
            ->method('setAttributeRequirements')
            ->with($this->equalTo(array('requirement1', 'requirement2', 'requirement3')));

        $this->assertSame($family, $this->processor->process($this->data));
    }

    protected function getRequirementData($channelCode, $attributeCode)
    {
        return array(
            'channel'   => $channelCode,
            'attribute' => $attributeCode,
            'required'  => true
        );
    }
}
