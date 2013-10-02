<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Builder;

use Oro\Bundle\EmailBundle\Builder\EmailEntityBatchProcessor;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Tests\Unit\ReflectionUtil;

class EmailEntityBatchProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailEntityBatchProcessor
     */
    private $batch;

    /**
     * @var EmailAddressManager
     */
    private $addrManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $ownerProvider;

    protected function setUp()
    {
        $this->ownerProvider = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addrManager = new EmailAddressManager(
            'Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures',
            'Test%sProxy'
        );
        $this->batch = new EmailEntityBatchProcessor($this->addrManager, $this->ownerProvider);
    }

    public function testAddEmail()
    {
        $this->batch->addEmail(new Email());
        $this->assertCount(1, ReflectionUtil::getProtectedProperty($this->batch, 'emails'));
    }

    public function testAddAddress()
    {
        $this->batch->addAddress($this->addrManager->newEmailAddress()->setEmail('Test@example.com'));
        $this->assertCount(1, ReflectionUtil::getProtectedProperty($this->batch, 'addresses'));

        $this->assertEquals('Test@example.com', $this->batch->getAddress('TeST@example.com')->getEmail());
        $this->assertNull($this->batch->getAddress('Another@example.com'));

        $this->setExpectedException('LogicException');
        $this->batch->addAddress($this->addrManager->newEmailAddress()->setEmail('TEST@example.com'));
    }

    public function testAddFolder()
    {
        $folder = new EmailFolder();
        $folder->setType('sent');
        $folder->setName('Test');
        $folder->setFullName('Test');
        $this->batch->addFolder($folder);
        $this->assertCount(1, ReflectionUtil::getProtectedProperty($this->batch, 'folders'));

        $this->assertEquals('Test', $this->batch->getFolder('sent', 'TeST')->getFullName());
        $this->assertNull($this->batch->getFolder('sent', 'Another'));

        $folder1 = new EmailFolder();
        $folder1->setType('trash');
        $folder1->setName('Test');
        $folder1->setFullName('Test');
        $this->batch->addFolder($folder1);
        $this->assertCount(2, ReflectionUtil::getProtectedProperty($this->batch, 'folders'));

        $this->assertEquals('Test', $this->batch->getFolder('trash', 'TeST')->getFullName());
        $this->assertNull($this->batch->getFolder('trash', 'Another'));

        $this->setExpectedException('LogicException');
        $folder2 = new EmailFolder();
        $folder2->setType('sent');
        $folder2->setName('TEST');
        $folder2->setFullName('TEST');
        $this->batch->addFolder($folder2);
    }

    public function testAddOrigin()
    {
        $origin = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\EmailOrigin')->getMock();
        $origin->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->batch->addOrigin($origin);
        $this->assertCount(1, ReflectionUtil::getProtectedProperty($this->batch, 'origins'));

        $this->assertEquals(1, $this->batch->getOrigin(1)->getId());
        $this->assertNull($this->batch->getOrigin(123));

        $this->setExpectedException('LogicException');
        $origin1 = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\EmailOrigin')->getMock();
        $origin1->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->batch->addOrigin($origin1);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPersist()
    {
        $origin = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\EmailOrigin')->getMock();
        $origin->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->batch->addOrigin($origin);

        $folder = new EmailFolder();
        $folder->setName('Exist');
        $folder->setFullName('Exist');
        $folder->setOrigin($origin);
        $this->batch->addFolder($folder);
        $newFolder = new EmailFolder();
        $newFolder->setName('New');
        $newFolder->setFullName('New');
        $newFolder->setOrigin($origin);
        $this->batch->addFolder($newFolder);

        $dbFolder = new EmailFolder();
        $dbFolder->setName('DbExist');
        $dbFolder->setFullName('DbExist');
        $dbFolder->setOrigin($origin);

        $addr = $this->addrManager->newEmailAddress()->setEmail('Exist');
        $this->batch->addAddress($addr);
        $newAddr = $this->addrManager->newEmailAddress()->setEmail('New');
        $this->batch->addAddress($newAddr);

        $dbAddr = $this->addrManager->newEmailAddress()->setEmail('DbExist');

        $email1 = new Email();
        $email1->setFolder($folder);
        $email1->setFromEmailAddress($addr);
        $email1Recip1 = new EmailRecipient();
        $email1Recip1->setEmailAddress($addr);
        $email1Recip2 = new EmailRecipient();
        $email1Recip2->setEmailAddress($newAddr);
        $email1->addRecipient($email1Recip1);
        $email1->addRecipient($email1Recip2);
        $this->batch->addEmail($email1);

        $email2 = new Email();
        $email2->setFolder($newFolder);
        $email2->setFromEmailAddress($newAddr);
        $email2Recip1 = new EmailRecipient();
        $email2Recip1->setEmailAddress($addr);
        $email2Recip2 = new EmailRecipient();
        $email2Recip2->setEmailAddress($newAddr);
        $email2->addRecipient($email2Recip1);
        $email2->addRecipient($email2Recip2);
        $this->batch->addEmail($email2);

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $folderRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $addrRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    array(
                        array('OroEmailBundle:EmailFolder', $folderRepo),
                        array('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\TestEmailAddressProxy', $addrRepo),
                    )
                )
            );

        $folderRepo->expects($this->exactly(2))
            ->method('findOneBy')
            ->will(
                $this->returnCallback(
                    function ($c) use (&$dbFolder) {
                        return $c['fullName'] === 'Exist' ? $dbFolder : null;
                    }
                )
            );
        $addrRepo->expects($this->exactly(2))
            ->method('findOneBy')
            ->will(
                $this->returnCallback(
                    function ($c) use (&$dbAddr) {
                        return $c['email'] === 'Exist' ? $dbAddr : null;
                    }
                )
            );

        $em->expects($this->exactly(4))
            ->method('persist')
            ->with(
                $this->logicalOr(
                    $this->identicalTo($newFolder),
                    $this->identicalTo($newAddr),
                    $this->identicalTo($email1),
                    $this->identicalTo($email2)
                )
            );

        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        $this->ownerProvider->expects($this->any())
            ->method('findEmailOwner')
            ->will($this->returnValue($owner));

        $this->batch->persist($em);

        $this->assertTrue($origin === $email1->getFolder()->getOrigin());
        $this->assertTrue($origin === $email2->getFolder()->getOrigin());
        $this->assertTrue($dbFolder === $email1->getFolder());
        $this->assertTrue($newFolder === $email2->getFolder());
        $this->assertTrue($dbAddr === $email1->getFromEmailAddress());
        $this->assertNull($email1->getFromEmailAddress()->getOwner());
        $this->assertTrue($newAddr === $email2->getFromEmailAddress());
        $this->assertTrue($owner === $email2->getFromEmailAddress()->getOwner());
        $email1Recipients = $email1->getRecipients();
        $this->assertTrue($dbAddr === $email1Recipients[0]->getEmailAddress());
        $this->assertNull($email1Recipients[0]->getEmailAddress()->getOwner());
        $this->assertTrue($newAddr === $email1Recipients[1]->getEmailAddress());
        $this->assertTrue($owner === $email1Recipients[1]->getEmailAddress()->getOwner());
        $email2Recipients = $email2->getRecipients();
        $this->assertTrue($dbAddr === $email2Recipients[0]->getEmailAddress());
        $this->assertNull($email2Recipients[0]->getEmailAddress()->getOwner());
        $this->assertTrue($newAddr === $email2Recipients[1]->getEmailAddress());
        $this->assertTrue($owner === $email2Recipients[1]->getEmailAddress()->getOwner());

    }
}
