<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;

class AddressCollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressCollectionType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new AddressCollectionType();
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

    public function testGetParent()
    {
        $this->assertEquals('collection', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_address_collection', $this->type->getName());
    }
}
