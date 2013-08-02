<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;

class EmailAttachmentContentTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailAttachmentContent();
        self::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testEmailAttachmentGetterAndSetter()
    {
        $emailAttachment = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAttachment');

        $entity = new EmailAttachmentContent();
        $entity->setEmailAttachment($emailAttachment);

        $this->assertTrue($emailAttachment === $entity->getEmailAttachment());
    }

    public function testValueGetterAndSetter()
    {
        $entity = new EmailAttachmentContent();
        $entity->setValue('test');
        $this->assertEquals('test', $entity->getValue());
    }

    public function testContentTransferEncodingGetterAndSetter()
    {
        $entity = new EmailAttachmentContent();
        $entity->setContentTransferEncoding('test');
        $this->assertEquals('test', $entity->getContentTransferEncoding());
    }

    private static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
