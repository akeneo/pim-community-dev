<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;

class PhoneCollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneCollectionType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new PhoneCollectionType();
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_collection', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_phone_collection', $this->type->getName());
    }
}
