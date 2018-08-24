<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter;

class AclVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testVote()
    {
        $selector = $this->getMockBuilder(AclExtensionSelector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $permissionMap = $this->createMock('Symfony\Component\Security\Acl\Permission\PermissionMapInterface');
        $voter = new AclVoter(
            $this->createMock('Symfony\Component\Security\Acl\Model\AclProviderInterface'),
            $this->createMock('Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface'),
            $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface'),
            $permissionMap
        );
        $voter->setAclExtensionSelector($selector);

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $object = new \stdClass();
        $extension = $this->createMock(AclExtensionInterface::class);
        $extension->expects($this->once())
            ->method('getAccessLevel')
            ->with($this->equalTo(1))
            ->will($this->returnValue(AccessLevel::LOCAL_LEVEL));

        $selector->expects($this->exactly(2))
            ->method('select')
            ->with($this->identicalTo($object))
            ->will($this->returnValue($extension));

        $inVoteToken = null;
        $inVoteObject = null;
        $inVoteExtension = null;

        $permissionMap->expects($this->exactly(2))
            ->method('getMasks')
            ->will(
                $this->returnCallback(
                    function () use (&$voter, &$inVoteToken, &$inVoteObject, &$inVoteExtension) {
                        $inVoteToken = $voter->getSecurityToken();
                        $inVoteObject = $voter->getObject();
                        $inVoteExtension = $voter->getAclExtension();

                        return null;
                    }
                )
            );

        $this->assertNull($voter->getSecurityToken());
        $this->assertNull($voter->getObject());
        $this->assertNull($voter->getAclExtension());

        $voter->vote($token, $object, ['test']);

        $this->assertNull($voter->getSecurityToken());
        $this->assertNull($voter->getObject());
        $this->assertNull($voter->getAclExtension());

        $this->assertTrue($token === $inVoteToken);
        $this->assertTrue($object === $inVoteObject);
        $this->assertTrue($extension === $inVoteExtension);
    }
}
