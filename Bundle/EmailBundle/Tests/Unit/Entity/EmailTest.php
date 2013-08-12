<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\Email;

class EmailTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new Email();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testSubjectGetterAndSetter()
    {
        $entity = new Email();
        $entity->setSubject('test');
        $this->assertEquals('test', $entity->getSubject());
    }

    public function testFromNameGetterAndSetter()
    {
        $entity = new Email();
        $entity->setFromName('test');
        $this->assertEquals('test', $entity->getFromName());
    }

    public function testFromEmailAddressGetterAndSetter()
    {
        $emailAddress = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAddress');

        $entity = new Email();
        $entity->setFromEmailAddress($emailAddress);

        $this->assertTrue($emailAddress === $entity->getFromEmailAddress());
    }

    public function testRecipientGetterAndSetter()
    {
        $toRecipient = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailRecipient');
        $toRecipient->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('to'));

        $ccRecipient = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailRecipient');
        $ccRecipient->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('cc'));

        $bccRecipient = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailRecipient');
        $bccRecipient->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bcc'));

        $entity = new Email();
        $entity->addRecipient($toRecipient);
        $entity->addRecipient($ccRecipient);
        $entity->addRecipient($bccRecipient);

        $recipients = $entity->getRecipients();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $recipients);
        $this->assertCount(3, $recipients);
        $this->assertTrue($toRecipient === $recipients[0]);
        $this->assertTrue($ccRecipient === $recipients[1]);
        $this->assertTrue($bccRecipient === $recipients[2]);

        $recipients = $entity->getRecipients('to');
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $recipients);
        $this->assertCount(1, $recipients);
        $this->assertTrue($toRecipient === $recipients->first());

        $recipients = $entity->getRecipients('cc');
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $recipients);
        $this->assertCount(1, $recipients);
        $this->assertTrue($ccRecipient === $recipients->first());

        $recipients = $entity->getRecipients('bcc');
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $recipients);
        $this->assertCount(1, $recipients);
        $this->assertTrue($bccRecipient === $recipients->first());
    }

    public function testReceivedAtGetterAndSetter()
    {
        $entity = new Email();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity->setReceivedAt($date);
        $this->assertEquals($date, $entity->getReceivedAt());
    }

    public function testSentAtGetterAndSetter()
    {
        $entity = new Email();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity->setSentAt($date);
        $this->assertEquals($date, $entity->getSentAt());
    }

    public function testImportanceGetterAndSetter()
    {
        $entity = new Email();
        $entity->setImportance(1);
        $this->assertEquals(1, $entity->getImportance());
    }

    public function testInternalDateGetterAndSetter()
    {
        $entity = new Email();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity->setInternalDate($date);
        $this->assertEquals($date, $entity->getInternalDate());
    }

    public function testMessageIdGetterAndSetter()
    {
        $entity = new Email();
        $entity->setMessageId('test');
        $this->assertEquals('test', $entity->getMessageId());
    }

    public function testXMessageIdGetterAndSetter()
    {
        $entity = new Email();
        $entity->setXMessageId('test');
        $this->assertEquals('test', $entity->getXMessageId());
    }

    public function testXThreadIdGetterAndSetter()
    {
        $entity = new Email();
        $entity->setXThreadId('test');
        $this->assertEquals('test', $entity->getXThreadId());
    }

    public function testFolderGetterAndSetter()
    {
        $folder = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailFolder');

        $entity = new Email();
        $entity->setFolder($folder);

        $this->assertTrue($folder === $entity->getFolder());
    }

    public function testEmailBodyGetterAndSetter()
    {
        $emailBody = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailBody');

        $entity = new Email();
        $entity->setEmailBody($emailBody);

        $this->assertTrue($emailBody === $entity->getEmailBody());
    }

    public function testBeforeSave()
    {
        $entity = new Email();
        $entity->beforeSave();

        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->assertEquals(Email::NORMAL_IMPORTANCE, $entity->getImportance());
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
