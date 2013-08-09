<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailRecipient;

class EmailRecipientTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailRecipient();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testNameGetterAndSetter()
    {
        $entity = new EmailRecipient();
        $entity->setName('test');
        $this->assertEquals('test', $entity->getName());
    }

    public function testTypeGetterAndSetter()
    {
        $entity = new EmailRecipient();
        $entity->setType('test');
        $this->assertEquals('test', $entity->getType());
    }

    public function testEmailAddressGetterAndSetter()
    {
        $emailAddress = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAddress');

        $entity = new EmailRecipient();
        $entity->setEmailAddress($emailAddress);

        $this->assertTrue($emailAddress === $entity->getEmailAddress());
    }

    public function testEmailGetterAndSetter()
    {
        $email = $this->getMock('Oro\Bundle\EmailBundle\Entity\Email');

        $entity = new EmailRecipient();
        $entity->setEmail($email);

        $this->assertTrue($email === $entity->getEmail());
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
