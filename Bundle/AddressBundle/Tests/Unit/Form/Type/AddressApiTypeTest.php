<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressApiType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormBuilder;
use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;

class AddressApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $buildAddressFormListener = $this->getMockBuilder('Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener')
            ->disableOriginalConstructor()
            ->getMock();
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new AddressApiType(
            $flexibleManager,
            'oro_address_value',
            $buildAddressFormListener
        );
    }

    public function testAddEntityFields()
    {
        /** @var \Symfony\Component\Form\FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(11))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->exactly(2))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $this->type->addEntityFields($builder);
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('address', $this->type->getName());
    }
}
