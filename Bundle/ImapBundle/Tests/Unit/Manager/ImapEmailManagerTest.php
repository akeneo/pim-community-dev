<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Manager\DTO;

use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;

class ImapEmailManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImapEmailManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $connector;

    protected function setUp()
    {
        $this->connector = $this->getMockBuilder('Oro\Bundle\ImapBundle\Connector\ImapConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new ImapEmailManager($this->connector);
    }

    public function testSelectFolder()
    {
        $this->assertEquals('inbox', $this->manager->getSelectedFolder());
        $this->manager->selectFolder('test');
        $this->assertEquals('test', $this->manager->getSelectedFolder());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetEmails()
    {
        /** @TODO remove skip after fix */
        $this->markTestSkipped('fix error');

        $uid = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $uid->expects($this->once())->method('getFieldValue')->will($this->returnValue('123'));

        $subject = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $subject->expects($this->once())->method('getFieldValue')->will($this->returnValue('Subject'));

        $fromEmail = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $fromEmail->expects($this->once())->method('getFieldValue')->will($this->returnValue('fromEmail'));

        $date = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $date->expects($this->once())->method('getFieldValue')->will($this->returnValue('2011-06-30 23:59:59 +0'));

        $received = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $received->expects($this->once())->method('getFieldValue')
            ->will($this->returnValue('by server to email; 2012-06-30 23:59:59 +0'));

        $intDate = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $intDate->expects($this->once())->method('getFieldValue')->will($this->returnValue('2013-06-30 23:59:59 +0'));

        $messageId = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $messageId->expects($this->once())->method('getFieldValue')->will($this->returnValue('MessageId'));

        $xMsgId = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $xMsgId->expects($this->once())->method('getFieldValue')->will($this->returnValue('XMsgId'));

        $xThrId = $this->getMock('Zend\Mail\Header\HeaderInterface');
        $xThrId->expects($this->once())->method('getFieldValue')->will($this->returnValue('XThrId'));

        $toAddress = $this->getMock('Zend\Mail\Address\AddressInterface');
        $toAddress->expects($this->once())->method('toString')->will($this->returnValue('toEmail'));
        $toAddressList = $this->getMockForAbstractClass(
            'Zend\Mail\Header\AbstractAddressList',
            array(),
            '',
            false,
            false,
            true,
            array('getAddressList')
        );
        $toAddressList->expects($this->once())->method('getAddressList')->will($this->returnValue(array($toAddress)));

        $ccAddress = $this->getMock('Zend\Mail\Address\AddressInterface');
        $ccAddress->expects($this->once())->method('toString')->will($this->returnValue('ccEmail'));
        $ccAddressList = $this->getMockForAbstractClass(
            'Zend\Mail\Header\AbstractAddressList',
            array(),
            '',
            false,
            false,
            true,
            array('getAddressList')
        );
        $ccAddressList->expects($this->once())->method('getAddressList')->will($this->returnValue(array($ccAddress)));

        $bccAddress = $this->getMock('Zend\Mail\Address\AddressInterface');
        $bccAddress->expects($this->once())->method('toString')->will($this->returnValue('bccEmail'));
        $bccAddressList = $this->getMockForAbstractClass(
            'Zend\Mail\Header\AbstractAddressList',
            array(),
            '',
            false,
            false,
            true,
            array('getAddressList')
        );
        $bccAddressList->expects($this->once())->method('getAddressList')->will($this->returnValue(array($bccAddress)));

        $this->connector->expects($this->once())
            ->method('getUidValidity')
            ->will($this->returnValue(456));
        $msg = $this->getMockBuilder('Oro\Bundle\ImapBundle\Mail\Storage\Message')
            ->disableOriginalConstructor()
            ->getMock();
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();
        $msg->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));
        $headers->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('UID', $uid),
                        array('Subject', $subject),
                        array('From', $fromEmail),
                        array('Date', $date),
                        array('Received', $received),
                        array('InternalDate', $intDate),
                        array('Importance', false),
                        array('Message-ID', $messageId),
                        array('X-GM-MSG-ID', $xMsgId),
                        array('X-GM-THR-ID', $xThrId),
                        array('X-GM-LABELS', false),
                        array('To', $toAddressList),
                        array('Cc', $ccAddressList),
                        array('Bcc', $bccAddressList),
                    )
                )
            );

        $query = $this->getMockBuilder('Oro\Bundle\ImapBundle\Connector\Search\SearchQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connector->expects($this->once())
            ->method('findItems')
            ->with($this->equalTo('Test Folder'), $this->identicalTo($query))
            ->will($this->returnValue(array($msg)));

        $this->manager->selectFolder('Test Folder');
        $emails = $this->manager->getEmails($query);

        $this->assertCount(1, $emails);

        $email = $emails[0];
        $this->assertEquals(123, $email->getId()->getUid());
        $this->assertEquals(456, $email->getId()->getUidValidity());
        $this->assertEquals('Subject', $email->getSubject());
        $this->assertEquals('fromEmail', $email->getFrom());
        $this->assertEquals(
            new \DateTime('2011-06-30 23:59:59', new \DateTimeZone('UTC')),
            $email->getSentAt()
        );
        $this->assertEquals(
            new \DateTime('2012-06-30 23:59:59', new \DateTimeZone('UTC')),
            $email->getReceivedAt()
        );
        $this->assertEquals(
            new \DateTime('2013-06-30 23:59:59', new \DateTimeZone('UTC')),
            $email->getInternalDate()
        );
        $this->assertEquals(0, $email->getImportance());
        $this->assertEquals('MessageId', $email->getMessageId());
        $this->assertEquals('XMsgId', $email->getXMessageId());
        $this->assertEquals('XThrId', $email->getXThreadId());
        $toRecipients = $email->getToRecipients();
        $this->assertEquals('toEmail', $toRecipients[0]);
        $ccRecipients = $email->getCcRecipients();
        $this->assertEquals('ccEmail', $ccRecipients[0]);
        $bccRecipients = $email->getBccRecipients();
        $this->assertEquals('bccEmail', $bccRecipients[0]);
    }
}
