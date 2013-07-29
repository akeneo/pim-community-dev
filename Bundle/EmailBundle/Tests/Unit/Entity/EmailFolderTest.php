<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailFolder;

class EmailFolderTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailFolder();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testNameGetterAndSetter()
    {
        $entity = new EmailFolder();
        $entity->setName('test');
        $this->assertEquals('test', $entity->getName());
    }

    public function testTypeGetterAndSetter()
    {
        $entity = new EmailFolder();
        $entity->setType('test');
        $this->assertEquals('test', $entity->getType());
    }

    public function testOriginGetterAndSetter()
    {
        $origin = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOrigin');

        $entity = new EmailFolder();
        $entity->setOrigin($origin);

        $this->assertTrue($origin === $entity->getOrigin());
    }

    public function testEmailGetterAndSetter()
    {
        $email = $this->getMock('Oro\Bundle\EmailBundle\Entity\Email');

        $entity = new EmailFolder();
        $entity->addEmail($email);

        $emails = $entity->getEmails();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $emails);
        $this->assertCount(1, $emails);
        $this->assertTrue($email === $emails[0]);
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
