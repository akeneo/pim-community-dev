<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\PermissionGrantingStrategyContext;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\User;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionMap;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\TreeBasedOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;

class PermissionGrantingStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionGrantingStrategy
     */
    private $strategy;

    /**
     * @var UserSecurityIdentity
     */
    private $sid;

    /**
     * @var PermissionGrantingStrategyContext
     */
    private $context;

    /** @var OwnerTree */
    private $ownerTree;

    /** @var OwnershipMetadataProvider */
    private $metadataProvider;

    protected function setUp()
    {
        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            $this->markTestSkipped('The Doctrine2 DBAL is required for this test');
        }

        $this->ownerTree = new OwnerTree();
        $this->metadataProvider = new OwnershipMetadataProvider();
        $objectClassAccessor = new ObjectClassAccessor();
        $decisionMaker = new TreeBasedOwnershipDecisionMaker(
            $this->ownerTree,
            $objectClassAccessor,
            new ObjectIdAccessor(),
            new ObjectOwnerAccessor($objectClassAccessor, $this->metadataProvider),
            $this->metadataProvider
        );
        $this->strategy = new PermissionGrantingStrategy(
            $decisionMaker,
            $this->metadataProvider
        );
        $this->context = new PermissionGrantingStrategyContext();
        $this->strategy->setContext($this->context);

        $user = new User(1);
        $this->sid = new UserSecurityIdentity('TestUser', get_class($user));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->context->setSecurityToken($token);
        $this->context->setObject('test');
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function testIsGrantedReturnsExceptionIfNoAceIsFound()
    {
        $acl = $this->getAcl();
        $this->strategy->isGranted($acl, array(1), array($this->sid));
    }

    public function testIsGrantedObjectAcesHavePriority()
    {
        $acl = $this->getAcl();
        $acl->insertClassAce($this->sid, 1);
        $acl->insertObjectAce($this->sid, 1, 0, false);
        $this->assertFalse($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    public function testIsGrantedUsesClassAcesIfNoApplicableObjectAceWasFound()
    {
        $acl = $this->getAcl();
        $acl->insertClassAce($this->sid, 1);
        $this->assertTrue($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    public function testObjIsGrantedUsesClassAcesIfNoApplicableObjectAceWasFound()
    {
        $obj = new TestObject(1);
        $this->context->setObject($obj);
        $masks = $this->getMasks('VIEW', $obj);

        $aceMask = $this->getMaskBuilder()
            ->add('VIEW_GLOBAL')
            ->get();

        $acl = $this->getAcl(ObjectIdentity::fromDomainObject($obj));
        $acl->insertClassAce($this->sid, $aceMask);
        $this->assertTrue($this->strategy->isGranted($acl, $masks, array($this->sid)));

        $this->metadataProvider->setMetadata(get_class($obj), $this->getOrganizationMetadata());
        $this->assertFalse($this->strategy->isGranted($acl, $masks, array($this->sid)));
        $this->metadataProvider->setMetadata(get_class($obj), $this->getBusinessUnitMetadata());
        $this->metadataProvider->setMetadata(get_class($obj), $this->getUserMetadata());
    }

    public function testIsGrantedFavorsLocalAcesOverParentAclAces()
    {
        $parentAcl = $this->getAcl();
        $parentAcl->insertClassAce($this->sid, 1, 0, false);

        $acl = $this->getAcl();
        $acl->setParentAcl($parentAcl);
        $acl->insertClassAce($this->sid, 1);

        $this->assertTrue($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    public function testIsGrantedUsesParentAcesIfNoLocalAcesAreApplicable()
    {
        $parentAcl = $this->getAcl();
        $parentAcl->insertClassAce($this->sid, 1);

        $anotherSid = $this->getRoleSid();
        $acl = $this->getAcl();
        $acl->setParentAcl($parentAcl);
        $acl->insertClassAce($anotherSid, 1, 0, false);

        $this->assertTrue($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function testIsGrantedUsesParentAcesOnlyIfInheritingIsSet()
    {
        $parentAcl = $this->getAcl();
        $parentAcl->insertClassAce($this->sid, 1);

        $anotherSid = $this->getRoleSid();
        $acl = $this->getAcl(null, false);
        $acl->setParentAcl($parentAcl);
        $acl->insertClassAce($anotherSid, 1, 0, false);

        $this->strategy->isGranted($acl, array(1), array($this->sid));
    }

    public function testIsGrantedFirstApplicableEntryMakesUltimateDecisionForPermissionIdentityCombination()
    {
        $anotherSid = $this->getRoleSid();

        $acl = $this->getAcl();
        $acl->insertClassAce($anotherSid, 1);

        $acl->insertClassAce($this->sid, 1, 1, false);
        $acl->insertClassAce($this->sid, 1, 2);
        $this->assertFalse($this->strategy->isGranted($acl, array(1), array($this->sid, $anotherSid)));

        $acl->insertObjectAce($this->sid, 1, 0, false);
        $acl->insertObjectAce($anotherSid, 1, 1);
        $this->assertFalse($this->strategy->isGranted($acl, array(1), array($this->sid, $anotherSid)));
    }

    public function testIsGrantedCallsAuditLoggerOnGrant()
    {
        $logger = $this->getMock('Symfony\Component\Security\Acl\Model\AuditLoggerInterface');
        $logger
            ->expects($this->once())
            ->method('logIfNeeded');
        $this->strategy->setAuditLogger($logger);

        $acl = $this->getAcl();
        $acl->insertObjectAce($this->sid, 1);
        $acl->updateObjectAuditing(0, true, false);

        $this->assertTrue($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    public function testIsGrantedCallsAuditLoggerOnDeny()
    {
        $logger = $this->getMock('Symfony\Component\Security\Acl\Model\AuditLoggerInterface');
        $logger
            ->expects($this->once())
            ->method('logIfNeeded');
        $this->strategy->setAuditLogger($logger);

        $acl = $this->getAcl();
        $acl->insertObjectAce($this->sid, 1, 0, false);
        $acl->updateObjectAuditing(0, false, true);

        $this->assertFalse($this->strategy->isGranted($acl, array(1), array($this->sid)));
    }

    /**
     * @dataProvider getAllStrategyTests
     */
    public function testIsGrantedStrategies($maskStrategy, $aceMask, $requiredMask, $result)
    {
        $acl = $this->getAcl();
        $acl->insertObjectAce($this->sid, $aceMask, 0, true, $maskStrategy);

        if (false === $result) {
            try {
                $this->strategy->isGranted($acl, array($requiredMask), array($this->sid));
                $this->fail('The ACE is not supposed to match.');
            } catch (NoAceFoundException $noAce) {
            }
        } else {
            $this->assertTrue($this->strategy->isGranted($acl, array($requiredMask), array($this->sid)));
        }
    }

    public function getAllStrategyTests()
    {
        return array(
            array('all', 1 << 0 | 1 << 1, 1 << 0, true),
            array('all', 1 << 0 | 1 << 1, 1 << 2, false),
            array('all', 1 << 0 | 1 << 10, 1 << 0 | 1 << 10, true),
            array('all', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1 || 1 << 2, false),
            array('any', 1 << 0 | 1 << 1, 1 << 0, true),
            array('any', 1 << 0 | 1 << 1, 1 << 0 | 1 << 2, true),
            array('any', 1 << 0 | 1 << 1, 1 << 2, false),
            array('equal', 1 << 0 | 1 << 1, 1 << 0, false),
            array('equal', 1 << 0 | 1 << 1, 1 << 1, false),
            array('equal', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1, true),
        );
    }

    protected function getAcl($oid = null, $entriesInheriting = true)
    {
        static $id = 1;

        return new Acl(
            $id++,
            $oid !== null ? $oid : new ObjectIdentity(1, 'Foo'),
            $this->strategy,
            array(),
            $entriesInheriting
        );
    }

    private function getRoleSid()
    {
        return new RoleSecurityIdentity('ROLE_USER');
    }

    private function getOrganizationMetadata()
    {
        return new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id');
    }

    private function getBusinessUnitMetadata()
    {
        return new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id');
    }

    private function getUserMetadata()
    {
        return new OwnershipMetadata('USER', 'owner', 'owner_id');
    }

    /**
     * @param string $permission
     * @param object $object
     * @return array|null may return null if permission/object combination is not supported
     */
    private function getMasks($permission, $object)
    {
        $map = new PermissionMap();
        return $map->getMasks($permission, $object);
    }

    /**
     * @return MaskBuilder
     */
    private function getMaskBuilder()
    {
        return new MaskBuilder();
    }
}
