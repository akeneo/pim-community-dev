<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

/**
 * Test related class
 *
  * @author    Gildas Quemener <gildas@akeneo.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
abstract class AttributeTypeTestCase extends \PHPUnit_Framework_TestCase
{
    protected $target;
    protected $name;
    protected $backendType;
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->guesser = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser');
        $this->guesser->expects($this->any())
            ->method('supportAttribute')
            ->will($this->returnValue(true));
        $this->guesser->expects($this->any())
            ->method('guessConstraints')
            ->will($this->returnValue(['constraints']));

        $this->target = $this->createAttributeType();
    }

    /**
     * Create attribute type to test
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeInterface
     */
    abstract protected function createAttributeType();

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        if (!$this->name) {
            throw new \Exception('You must define the $name property.');
        }
        $this->assertEquals($this->name, $this->target->getName());
    }

    /**
     * @throws \Exception
     */
    public function testGetFormType()
    {
        if (!$this->formType) {
            throw new \Exception('You must define the $formType property.');
        }
        $this->assertEquals($this->formType, $this->target->getFormType());
    }

    /**
     * @throws \Exception
     */
    public function testGetBackendType()
    {
        if (!$this->backendType) {
            throw new \Exception('You must define the $backendType property.');
        }
        $this->assertEquals($this->backendType, $this->target->getBackendType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertInstanceOfAbstractAttributeType()
    {
        if (!$this->target) {
            throw new \Exception(sprintf('You must override the setUp() method and provide a $target instance.'));
        }
        $this->assertInstanceOf('Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType', $this->target);
    }

    /**
     * @return \Symfony\Component\Form\FormFactory
     */
    protected function getFormFactoryMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $options
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface
     */
    protected function getFlexibleValueMock(array $options)
    {
        $options = array_merge(
            [
                'data'         => null,
                'defaultValue' => null,
                'backendType'  => null,
                'attribute_options' => []
            ],
            $options
        );

        $value = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface',
            ['getAttribute', 'getData']
        );

        $attributeMock = $this->getAttributeMock(
            $options['backendType'],
            $options['defaultValue'],
            $options['attribute_options']
        );

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock));

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($options['data']));

        return $value;
    }

    /**
     * @param string $backendType
     * @param mixed  $defaultValue
     * @param array  $attributeOptions
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeInterface
     */
    protected function getAttributeMock($backendType, $defaultValue, array $attributeOptions = [])
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attribute->expects($this->any())
            ->method('getDefaultValue')
            ->will($this->returnValue($defaultValue));

        return $attribute;
    }
}
