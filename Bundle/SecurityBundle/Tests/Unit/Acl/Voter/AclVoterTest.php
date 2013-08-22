<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter;

class AclVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testVote()
    {
        $permissionMap = $this->getMock('Symfony\Component\Security\Acl\Permission\PermissionMapInterface');
        $voter = new AclVoter(
            $this->getMock('Symfony\Component\Security\Acl\Model\AclProviderInterface'),
            $this->getMock('Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface'),
            $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface'),
            $permissionMap
        );

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $object = new \stdClass();

        $inVoteToken = null;
        $inVoteObject = null;

        $permissionMap->expects($this->once())
            ->method('getMasks')
            ->will(
                $this->returnCallback(
                    function () use (&$voter, &$inVoteToken, &$inVoteObject) {
                        $inVoteToken = $voter->getSecurityToken();
                        $inVoteObject = $voter->getObject();

                        return null;
                    }
                )
            );

        $this->assertNull($voter->getSecurityToken());
        $this->assertNull($voter->getObject());

        $voter->vote($token, $object, array('test'));

        $this->assertNull($voter->getSecurityToken());
        $this->assertNull($voter->getObject());

        $this->assertTrue($token === $inVoteToken);
        $this->assertTrue($object === $inVoteObject);
    }
}
