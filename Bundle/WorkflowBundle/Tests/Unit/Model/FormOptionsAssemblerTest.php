<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Action\Configurable;
use Oro\Bundle\WorkflowBundle\Model\FormOptionsAssembler;
use Oro\Bundle\WorkflowBundle\Model\Attribute;

class FormOptionsAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationPass;

    /**
     * @var FormOptionsAssembler
     */
    protected $assembler;

    protected function setUp()
    {
        $this->actionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationPass = $this->getMockBuilder(
            'Oro\Bundle\WorkflowBundle\Model\ConfigurationPass\ConfigurationPassInterface'
        )->getMockForAbstractClass();

        $this->assembler = new FormOptionsAssembler($this->actionFactory);
        $this->assembler->addConfigurationPass($this->configurationPass);
    }

    public function testAssemble()
    {
        $options = array(
            'attribute_fields' => array(
                'attribute_one' => array('form_type' => 'text'),
                'attribute_two' => array('form_type' => 'text'),
            ),
            'attribute_default_values' => array(
                'attribute_one' => '$foo',
                'attribute_two' => '$bar',
            ),
            'init_actions' => array(
                array('@foo' => 'bar')
            )
        );

        $expectedOptions = array(
            'attribute_fields' => array(
                'attribute_one' => array('form_type' => 'text'),
                'attribute_two' => array('form_type' => 'text'),
            ),
            'attribute_default_values' => array(
                'attribute_one' => new PropertyPath('data.foo'),
                'attribute_two' => new PropertyPath('data.bar'),
            ),
            'init_actions' => $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface')
        );

        $attributes = array(
            $this->createAttribute('attribute_one'),
            $this->createAttribute('attribute_two'),
        );

        $this->configurationPass->expects($this->once())
            ->method('passConfiguration')
            ->with($options['attribute_default_values'])
            ->will($this->returnValue($expectedOptions['attribute_default_values']));

        $this->actionFactory->expects($this->once())
            ->method('create')
            ->with(Configurable::ALIAS, $options['init_actions'])
            ->will($this->returnValue($expectedOptions['init_actions']));

        $this->assertEquals(
            $expectedOptions,
            $this->assembler->assemble(
                $options,
                $attributes,
                'step',
                'test'
            )
        );
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     */
    public function testAssembleRequiredOptionException(
        $options,
        $attributes,
        $owner,
        $ownerName,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->assembler->assemble($options, $attributes, $owner, $ownerName);
    }

    public function invalidOptionsDataProvider()
    {
        return array(
            'string_attribute_fields' => array(
                'options' => array(
                    'attribute_fields' => 'string'
                ),
                'attributes' => array(),
                'owner' => FormOptionsAssembler::STEP_OWNER,
                'ownerName' => 'test',
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' => 'Option "form_options.attribute_fields" at step "test" must be an array.'
            ),
            'string_attribute_default_values' => array(
                'options' => array(
                    'attribute_default_values' => 'string'
                ),
                'attributes' => array(),
                'owner' => FormOptionsAssembler::STEP_OWNER,
                'ownerName' => 'test',
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' =>
                    'Option "form_options.attribute_default_values" of step "test" must be an array.'
            ),
            'attribute_not_exist_at_attribute_fields' => array(
                'options' => array(
                    'attribute_fields' => array(
                        'attribute_one' => array('form_type' => 'text'),
                    )
                ),
                'attributes' => array(),
                'owner' => FormOptionsAssembler::STEP_OWNER,
                'ownerName' => 'test',
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException',
                'expectedExceptionMessage' => 'Unknown attribute "attribute_one" at step "test".'
            ),
            'attribute_not_exist_at_attribute_default_values' => array(
                'options' => array(
                    'attribute_default_values' => array(
                        'attribute_one' => array('form_type' => 'text'),
                    )
                ),
                'attributes' => array(),
                'owner' => FormOptionsAssembler::STEP_OWNER,
                'ownerName' => 'test',
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException',
                'expectedExceptionMessage' => 'Unknown attribute "attribute_one" at step "test".'
            ),
            'attribute_default_value_not_in_attribute_fields' => array(
                'options' => array(
                    'attribute_fields' => array(
                        'attribute_one' => array('form_type' => 'text'),
                    ),
                    'attribute_default_values' => array(
                        'attribute_two' => '$attribute_one'
                    )
                ),
                array(
                    $this->createAttribute('attribute_one'),
                    $this->createAttribute('attribute_two'),
                ),
                'owner' => FormOptionsAssembler::STEP_OWNER,
                'ownerName' => 'test',
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' =>
                    'Form options of step "test" doesn\'t have attribute "attribute_two" which is referenced in ' .
                    '"attribute_default_values" option.'
            ),
        );
    }

    /**
     * @param string $name
     * @return Attribute
     */
    protected function createAttribute($name)
    {
        $attribute = new Attribute();
        $attribute->setName($name);

        return $attribute;
    }
}
