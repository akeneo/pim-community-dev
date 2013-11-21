<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

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

    public function testGetParent()
    {
        $this->assertEquals('oro_collection', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_address_collection', $this->type->getName());
    }
}
