<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;

class AclManagerSidTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclSidManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aclProvider;

    protected function setUp(): void
    {
        $this->aclProvider = $this->getMockBuilder(MutableAclProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new AclSidManager(
            $this->aclProvider
        );
    }

    public function testIsAclEnabled()
    {
        $manager = new AclSidManager();
        $this->assertFalse($manager->isAclEnabled());
        $aclProvider = $this->getMockBuilder(MutableAclProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager = new AclSidManager($aclProvider);

        $this->assertTrue($manager->isAclEnabled());
    }

    public function testUpdateSid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->aclProvider->expects($this->once())
            ->method('updateSecurityIdentity')
            ->with($this->identicalTo($sid), $this->equalTo('old'));

        $this->manager->updateSid($sid, 'old');
    }

    public function testDeleteSid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->aclProvider->expects($this->once())
            ->method('deleteSecurityIdentity')
            ->with($this->identicalTo($sid));

        $this->manager->deleteSid($sid);
    }
}
