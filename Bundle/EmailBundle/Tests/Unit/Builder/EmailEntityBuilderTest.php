<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Builder;

use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;

class EmailEntityBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailEntityBuilder
     */
    private $builder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $batch;

    protected function setUp()
    {
        $this->batch = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBatchCooker')
            ->disableOriginalConstructor()
            ->getMock();
        $addrManager = new EmailAddressManager('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures', 'Test%sProxy');
        $this->builder = new EmailEntityBuilder($this->batch, $addrManager);
    }

    private function initEmailStorage()
    {
        $storage = array();
        $this->batch->expects($this->any())
            ->method('getAddress')
            ->will($this->returnCallback(function ($email) use (&$storage) {
                return isset($storage[$email]) ? $storage[$email] : null;
            }));
        $this->batch->expects($this->any())
            ->method('addAddress')
            ->will($this->returnCallback(function ($obj) use (&$storage) {
                $storage[$obj->getEmail()] = $obj;
            }));
    }

    public function testEmail()
    {
        $this->initEmailStorage();

        $date = new \DateTime('now');
        $email = $this->builder->email(
            'testSubject',
            '"Test" <test@example.com>',
            '"Test1" <test1@example.com>',
            $date,
            $date,
            $date,
            Email::NORMAL_IMPORTANCE,
            array('"Test2" <test2@example.com>', 'test1@example.com'));

        $this->assertEquals('testSubject', $email->getSubject());
        $this->assertEquals('"Test" <test@example.com>', $email->getFromName());
        $this->assertEquals('test@example.com', $email->getFromEmailAddress()->getEmail());
        $this->assertEquals($date, $email->getSentAt());
        $this->assertEquals($date, $email->getReceivedAt());
        $this->assertEquals($date, $email->getInternalDate());
        $this->assertEquals(Email::NORMAL_IMPORTANCE, $email->getImportance());
        $to = $email->getRecipients(EmailRecipient::TO);
        $this->assertEquals('"Test1" <test1@example.com>', $to[0]->getName());
        $this->assertEquals('test1@example.com', $to[0]->getEmailAddress()->getEmail());
        $cc = $email->getRecipients(EmailRecipient::CC);
        $this->assertEquals('"Test2" <test2@example.com>', $cc[1]->getName());
        $this->assertEquals('test2@example.com', $cc[1]->getEmailAddress()->getEmail());
        $this->assertEquals('test1@example.com', $cc[2]->getName());
        $this->assertEquals('test1@example.com', $cc[2]->getEmailAddress()->getEmail());
        $bcc = $email->getRecipients(EmailRecipient::BCC);
        $this->assertCount(0, $bcc);
    }

    public function testToRecipient()
    {
        $this->initEmailStorage();
        $result = $this->builder->recipientTo('"Test" <test@example.com>');

        $this->assertEquals(EmailRecipient::TO, $result->getType());
        $this->assertEquals('"Test" <test@example.com>', $result->getName());
        $this->assertEquals('test@example.com', $result->getEmailAddress()->getEmail());
    }

    public function testCcRecipient()
    {
        $this->initEmailStorage();
        $result = $this->builder->recipientCc('"Test" <test@example.com>');

        $this->assertEquals(EmailRecipient::CC, $result->getType());
        $this->assertEquals('"Test" <test@example.com>', $result->getName());
        $this->assertEquals('test@example.com', $result->getEmailAddress()->getEmail());
    }

    public function testBccRecipient()
    {
        $this->initEmailStorage();
        $result = $this->builder->recipientBcc('"Test" <test@example.com>');

        $this->assertEquals(EmailRecipient::BCC, $result->getType());
        $this->assertEquals('"Test" <test@example.com>', $result->getName());
        $this->assertEquals('test@example.com', $result->getEmailAddress()->getEmail());
    }

    public function testOrigin()
    {
        $storage = array();
        $this->batch->expects($this->exactly(2))
            ->method('getOrigin')
            ->will($this->returnCallback(function ($name) use (&$storage) {
                return isset($storage[$name]) ? $storage[$name] : null;
            }));
        $this->batch->expects($this->once())
            ->method('addOrigin')
            ->will($this->returnCallback(function ($obj) use (&$storage) {
                $storage[$obj->getName()] = $obj;
            }));

        $result = $this->builder->origin('test');

        $this->assertEquals('test', $result->getName());
        $this->assertTrue($result === $this->builder->origin('test'));
    }

    public function testFolder()
    {
        $storage = array();
        $this->batch->expects($this->exactly(10))
            ->method('getFolder')
            ->will($this->returnCallback(function ($type, $name) use (&$storage) {
                return isset($storage[$type . $name]) ? $storage[$type . $name] : null;
            }));
        $this->batch->expects($this->exactly(5))
            ->method('addFolder')
            ->will($this->returnCallback(function ($obj) use (&$storage) {
                $storage[$obj->getType() . $obj->getName()] = $obj;
            }));

        $inbox = $this->builder->folderInbox('test');
        $sent = $this->builder->folderSent('test');
        $drafts = $this->builder->folderDrafts('test');
        $trash = $this->builder->folderTrash('test');
        $other = $this->builder->folderOther('test');

        $this->assertEquals('test', $inbox->getName());
        $this->assertEquals('test', $sent->getName());
        $this->assertEquals('test', $drafts->getName());
        $this->assertEquals('test', $trash->getName());
        $this->assertEquals('test', $other->getName());
        $this->assertTrue($inbox === $this->builder->folderInbox('test'));
        $this->assertTrue($sent === $this->builder->folderSent('test'));
        $this->assertTrue($drafts === $this->builder->folderDrafts('test'));
        $this->assertTrue($trash === $this->builder->folderTrash('test'));
        $this->assertTrue($other === $this->builder->folderOther('test'));
    }

    public function testBody()
    {
        $body = $this->builder->body('testContent', true, true);

        $this->assertEquals('testContent', $body->getContent());
        $this->assertFalse($body->getBodyIsText());
        $this->assertTrue($body->getPersistent());
    }

    public function testAttachment()
    {
        $attachment = $this->builder->attachment('testFileName', 'testContentType');

        $this->assertEquals('testFileName', $attachment->getFileName());
        $this->assertEquals('testContentType', $attachment->getContentType());
    }

    public function testAttachmentContent()
    {
        $attachmentContent = $this->builder->attachmentContent('testContent', 'testEncoding');

        $this->assertEquals('testContent', $attachmentContent->getValue());
        $this->assertEquals('testEncoding', $attachmentContent->getContentTransferEncoding());
    }

    public function testGetBatch()
    {
        $this->assertTrue($this->batch === $this->builder->getBatch());
    }
}
