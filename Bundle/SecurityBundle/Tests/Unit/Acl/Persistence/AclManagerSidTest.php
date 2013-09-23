<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AclManagerSidTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclSidManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aclProvider;

    protected function setUp()
    {
        $this->aclProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider')
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
        $aclProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $manager = new AclSidManager($aclProvider);

        $this->assertTrue($manager->isAclEnabled());
    }

    public function testGetSid()
    {
        $this->assertEquals(
            new RoleSecurityIdentity('ROLE_TEST'),
            $this->manager->getSid('ROLE_TEST')
        );

        $src = $this->getMock('Symfony\Component\Security\Core\Role\RoleInterface');
        $src->expects($this->once())
            ->method('getRole')
            ->will($this->returnValue('ROLE_TEST'));
        $this->assertEquals(
            new RoleSecurityIdentity('ROLE_TEST'),
            $this->manager->getSid($src)
        );

        $src = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $src->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Test'));
        $this->assertEquals(
            new UserSecurityIdentity('Test', get_class($src)),
            $this->manager->getSid($src)
        );

        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Test'));
        $src = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $src->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->assertEquals(
            new UserSecurityIdentity('Test', get_class($user)),
            $this->manager->getSid($src)
        );

        $this->setExpectedException('\InvalidArgumentException');
        $this->manager->getSid(new \stdClass());
    }

    public function testUpdateSid()
    {
        $sid = $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->aclProvider->expects($this->once())
            ->method('updateSecurityIdentity')
            ->with($this->identicalTo($sid), $this->equalTo('old'));

        $this->manager->updateSid($sid, 'old');
    }

    public function testDeleteSid()
    {
        $sid = $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->aclProvider->expects($this->once())
            ->method('deleteSecurityIdentity')
            ->with($this->identicalTo($sid));

        $this->manager->deleteSid($sid);
    }
}
