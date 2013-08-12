<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailBody;

class EmailBodyTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailBody();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testContentGetterAndSetter()
    {
        $entity = new EmailBody();
        $entity->setContent('test');
        $this->assertEquals('test', $entity->getContent());
    }

    public function testBodyIsTextGetterAndSetter()
    {
        $entity = new EmailBody();
        $entity->setBodyIsText(true);
        $this->assertEquals(true, $entity->getBodyIsText());
    }

    public function testHasAttachmentsGetterAndSetter()
    {
        $entity = new EmailBody();
        $entity->setHasAttachments(true);
        $this->assertEquals(true, $entity->getHasAttachments());
    }

    public function testPersistentGetterAndSetter()
    {
        $entity = new EmailBody();
        $entity->setPersistent(true);
        $this->assertEquals(true, $entity->getPersistent());
    }

    public function testHeaderGetterAndSetter()
    {
        $email = $this->getMock('Oro\Bundle\EmailBundle\Entity\Email');

        $entity = new EmailBody();
        $entity->setHeader($email);

        $this->assertTrue($email === $entity->getHeader());
    }

    public function testAttachmentGetterAndSetter()
    {
        $attachment = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAttachment');

        $entity = new EmailBody();
        $entity->addAttachment($attachment);

        $attachments = $entity->getAttachments();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attachments);
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachment === $attachments[0]);
    }

    public function testBeforeSave()
    {
        $entity = new EmailBody();
        $entity->beforeSave();

        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->assertEquals(false, $entity->getBodyIsText());
        $this->assertEquals(false, $entity->getHasAttachments());
        $this->assertEquals(false, $entity->getPersistent());
        $this->assertGreaterThanOrEqual($createdAt, $entity->getCreatedAt());
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
