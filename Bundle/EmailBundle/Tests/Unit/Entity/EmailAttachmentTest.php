<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailAttachment;

class EmailAttachmentTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailAttachment();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testFileNameGetterAndSetter()
    {
        $entity = new EmailAttachment();
        $entity->setFileName('test');
        $this->assertEquals('test', $entity->getFileName());
    }

    public function testContentTypeGetterAndSetter()
    {
        $entity = new EmailAttachment();
        $entity->setContentType('test');
        $this->assertEquals('test', $entity->getContentType());
    }

    public function testContentGetterAndSetter()
    {
        $content = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent');

        $entity = new EmailAttachment();
        $entity->setContent($content);

        $this->assertTrue($content === $entity->getContent());
    }

    public function testEmailBodyGetterAndSetter()
    {
        $emailBody = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailBody');

        $entity = new EmailAttachment();
        $entity->setEmailBody($emailBody);

        $this->assertTrue($emailBody === $entity->getEmailBody());
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
