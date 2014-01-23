<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleValueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @var FlexibleValueType
     */
    protected $type;

    protected function setUp()
    {
        $flexibleManager = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $flexibleManager->expects($this->once())
            ->method('getFlexibleValueName')
            ->will($this->returnValue('Acme\\DemoBundle\\Entity\\TestEntity'));

        $this->subscriber = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')
            ->getMock();

        $this->type = new FlexibleValueType($flexibleManager, $this->subscriber);
    }

    public function testConstructor()
    {
        $this->assertAttributeEquals('Acme\\DemoBundle\\Entity\\TestEntity', 'valueClass', $this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('add')
            ->with('id', 'hidden');
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->subscriber);

        $this->type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->getMock();
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($optionsResolver);
    }

    public function testGetName()
    {
        $this->assertEquals('pim_flexibleentity_value', $this->type->getName());
    }
}
