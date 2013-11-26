<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;

class EmailCollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailCollectionType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new EmailCollectionType();
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_collection', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_email_collection', $this->type->getName());
    }
}
