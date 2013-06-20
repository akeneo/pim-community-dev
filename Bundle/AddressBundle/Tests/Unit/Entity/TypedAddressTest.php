<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\TypedAddress;

class TypedAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new TypedAddress();
        $obj->setType('TEST');
        $this->assertEquals('TEST', $obj->getType());
    }
}
