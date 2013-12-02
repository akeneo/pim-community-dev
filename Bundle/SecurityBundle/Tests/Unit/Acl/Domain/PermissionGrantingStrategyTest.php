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
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProviderStub;
use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionMap;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;

class PermissionGrantingStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionMap
     */
    private $map;

    /**
     * @var AclExtensionSelector
     */
    private $selector;

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

    /** @var OwnershipMetadataProviderStub */
    private $metadataProvider;

    protected function setUp()
    {
        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            $this->markTestSkipped('The Doctrine2 DBAL is required for this test');
        }

        $this->ownerTree = new OwnerTree();
        $this->metadataProvider = new OwnershipMetadataProviderStub($this);
        $classAccessor = new EntityClassAccessor();
        $objectIdAccessor = new ObjectIdAccessor();

        $treeProviderMock = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $treeProviderMock->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($this->ownerTree));

        $decisionMaker = new EntityOwnershipDecisionMaker(
            $treeProviderMock,
            $classAccessor,
            $objectIdAccessor,
            new EntityOwnerAccessor($classAccessor, $this->metadataProvider),
            $this->metadataProvider
        );
        $this->strategy = new PermissionGrantingStrategy(
            $decisionMaker,
            $this->metadataProvider
        );
        $this->selector = TestHelper::get($this)->createAclExtensionSelector($this->metadataProvider);
        $this->context = new PermissionGrantingStrategyContext($this->selector);
        $contextLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $contextLink->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($this->context));
        $this->strategy->setContext($contextLink);

        $user = new User(1);
        $this->sid = new UserSecurityIdentity('TestUser', get_class($user));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->context->setSecurityToken($token);
        $this->context->setObject(new TestEntity('testId'));

        $this->map = new PermissionMap($this->selector);
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
        $obj = new TestEntity(1);
        $this->context->setObject($obj);
        $masks = $this->getMasks('VIEW', $obj);

        $aceMask = $this->getMaskBuilder('VIEW', $obj)
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
            array('all', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1 | 1 << 2, false),
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

        if ($oid === null) {
            $oid = new ObjectIdentity($this->context->getObject()->getId(), get_class($this->context->getObject()));
        }

        return new Acl(
            $id++,
            $oid,
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
     * @param  string     $permission
     * @param  object     $object
     * @return array|null may return null if permission/object combination is not supported
     */
    private function getMasks($permission, $object)
    {
        return $this->map->getMasks($permission, $object);
    }

    /**
     * @param  string      $permission
     * @param  mixed       $object
     * @return MaskBuilder
     */
    private function getMaskBuilder($permission, $object)
    {
        return $this->selector->select($object)->getMaskBuilder($permission);
    }
}
