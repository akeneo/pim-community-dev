<?php

namespace Oro\Bundle\UserBundle\Tests\AttributeType;

use Oro\Bundle\UserBundle\AttributeType\UserType;

class UserTypeTest extends \PHPUnit_Framework_TestCase
{
    const USER_TYPE = 'oro_user_attribute_user';
    public function testGetName()
    {
        /** @var  UserType $userType */
        $userType = $this->getMockBuilder('Oro\Bundle\UserBundle\AttributeType\UserType')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertEquals(self::USER_TYPE, $userType->getName());
    }
}
