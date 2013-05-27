<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;

class AddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressType
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

        $this->type = new AddressType(
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

        $builder->expects($this->exactly(10))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->at(0))
            ->method('add')
            ->with('id', 'hidden');

        $builder->expects($this->at(1))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $builder->expects($this->at(2))
            ->method('add')
            ->with('firstName', 'text');

        $builder->expects($this->at(3))
            ->method('add')
            ->with('lastName', 'text');

        $builder->expects($this->at(4))
            ->method('add')
            ->with('street', 'text');

        $builder->expects($this->at(5))
            ->method('add')
            ->with('street2', 'text');

        $builder->expects($this->at(6))
            ->method('add')
            ->with('city', 'text');

        $builder->expects($this->at(7))
            ->method('add')
            ->with('state', 'oro_region');

        $builder->expects($this->at(8))
            ->method('add')
            ->with('state_text', 'hidden');

        $builder->expects($this->at(9))
            ->method('add')
            ->with('country', 'oro_country');

        $builder->expects($this->at(10))
            ->method('add')
            ->with('postalCode', 'text');

        $builder->expects($this->once())
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
        $this->assertEquals('oro_address', $this->type->getName());
    }
}
