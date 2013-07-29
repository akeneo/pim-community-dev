<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailAddress;

class EmailAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailAddress();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testEmailAddressGetterAndSetter()
    {
        $entity = new EmailAddress();
        $entity->setEmailAddress('test');
        $this->assertEquals('test', $entity->getEmailAddress());
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
