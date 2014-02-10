<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AttributeTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $target;
    protected $name;

    /**
     * Default set of options to create named form child
     *
     * @var array
     */
    protected $defaultCreateNamedOptions = array(
        'constraints'     => array('constraints'),
        'label'           => null,
        'required'        => null,
        'auto_initialize' => null
    );

    protected function setUp()
    {
        $this->guesser = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser');
        $this->guesser->expects($this->any())
            ->method('supportAttribute')
            ->will($this->returnValue(true));
        $this->guesser->expects($this->any())
            ->method('guessConstraints')
            ->will($this->returnValue(array('constraints')));
    }

    public function testGetName()
    {
        if (!$this->target) {
            throw new \Exception(sprintf('You must override the setUp() method and provide a $target instance.'));
        }

        if (!$this->name) {
            throw new \Exception(sprintf('You must override the $name property.'));
        }
        $this->assertEquals($this->name, $this->target->getName());
    }

    public function testAssertInstanceOfAbstractAttributeType()
    {
        if (!$this->target) {
            throw new \Exception(sprintf('You must override the setUp() method and provide a $target instance.'));
        }
        $this->assertInstanceOf('Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType', $this->target);
    }

    protected function getFormFactoryMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFlexibleValueMock(array $options)
    {
        $options = array_merge(
            array(
                'data'         => null,
                'defaultValue' => null,
                'backendType'  => null,
            ),
            $options
        );

        $value = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface'
        );

        $value->expects($this->any())
            ->method('getAttribute')
            ->will(
                $this->returnValue(
                    $this->getAttributeMock(
                        $options['backendType'],
                        $options['defaultValue']
                    )
                )
            );

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($options['data']));

        return $value;
    }

    protected function getAttributeMock($backendType, $defaultValue)
    {
        $attribute = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute');

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attribute->expects($this->any())
            ->method('getDefaultValue')
            ->will($this->returnValue($defaultValue));

        return $attribute;
    }
}
