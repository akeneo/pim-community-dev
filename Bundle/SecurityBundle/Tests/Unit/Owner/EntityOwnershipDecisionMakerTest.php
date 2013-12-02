<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProviderStub;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User;

class EntityOwnershipDecisionMakerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnerTree
     */
    private $tree;

    /**
     * @var OwnershipMetadataProviderStub
     */
    private $metadataProvider;

    /**
     * @var EntityOwnershipDecisionMaker
     */
    private $decisionMaker;

    /** @var Organization */
    private $org1;
    /** @var Organization */
    private $org2;
    /** @var Organization */
    private $org3;
    /** @var Organization */
    private $org4;

    /** @var BusinessUnit */
    private $bu1;
    /** @var BusinessUnit */
    private $bu2;
    /** @var BusinessUnit */
    private $bu3;
    /** @var BusinessUnit */
    private $bu31;
    /** @var BusinessUnit */
    private $bu4;
    /** @var BusinessUnit */
    private $bu41;
    /** @var BusinessUnit */
    private $bu411;

    /** @var User */
    private $user1;
    /** @var User */
    private $user2;
    /** @var User */
    private $user3;
    /** @var User */
    private $user31;
    /** @var User */
    private $user4;
    /** @var User */
    private $user411;

    protected function setUp()
    {
        $this->tree = new OwnerTree();

        $this->metadataProvider = new OwnershipMetadataProviderStub($this);
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getOrganizationClass(),
            new OwnershipMetadata()
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getBusinessUnitClass(),
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getUserClass(),
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $treeProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $treeProvider->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($this->tree));

        $classAccessor = new EntityClassAccessor();
        $this->decisionMaker = new EntityOwnershipDecisionMaker(
            $treeProvider,
            $classAccessor,
            new ObjectIdAccessor(),
            new EntityOwnerAccessor($classAccessor, $this->metadataProvider),
            $this->metadataProvider
        );
    }

    private function buildTestTree()
    {
        /**
         * org1  org2     org3         org4
         *                |            |
         *  bu1   bu2     +-bu3        +-bu4
         *        |       | |            |
         *        |       | +-bu31       |
         *        |       | | |          |
         *        |       | | +-user31   |
         *        |       | |            |
         *  user1 +-user2 | +-user3      +-user4
         *                |                |
         *                +-bu3a           +-bu3
         *                  |              +-bu4
         *                  +-bu3a1          |
         *                                   +-bu41
         *                                     |
         *                                     +-bu411
         *                                       |
         *                                       +-user411
         */
        $this->org1 = new Organization('org1');
        $this->org2 = new Organization('org2');
        $this->org3 = new Organization('org3');
        $this->org4 = new Organization('org4');

        $this->bu1 = new BusinessUnit('bu1');
        $this->bu2 = new BusinessUnit('bu2');
        $this->bu3 = new BusinessUnit('bu3');
        $this->bu31 = new BusinessUnit('bu31', $this->bu3);
        $this->bu4 = new BusinessUnit('bu4');
        $this->bu41 = new BusinessUnit('bu41', $this->bu4);
        $this->bu411 = new BusinessUnit('bu411', $this->bu41);

        $this->user1 = new User('user1');
        $this->user2 = new User('user2', $this->bu2);
        $this->user3 = new User('user3', $this->bu3);
        $this->user31 = new User('user31', $this->bu31);
        $this->user4 = new User('user4', $this->bu4);
        $this->user411 = new User('user411', $this->bu411);

        $this->tree->addBusinessUnit('bu1', null);
        $this->tree->addBusinessUnit('bu2', null);
        $this->tree->addBusinessUnit('bu3', 'org3');
        $this->tree->addBusinessUnit('bu31', 'org3');
        $this->tree->addBusinessUnit('bu3a', 'org3');
        $this->tree->addBusinessUnit('bu3a1', 'org3');
        $this->tree->addBusinessUnit('bu4', 'org4');
        $this->tree->addBusinessUnit('bu41', 'org4');
        $this->tree->addBusinessUnit('bu411', 'org4');

        $this->tree->addBusinessUnitRelation('bu1', null);
        $this->tree->addBusinessUnitRelation('bu2', null);
        $this->tree->addBusinessUnitRelation('bu3', null);
        $this->tree->addBusinessUnitRelation('bu31', 'bu3');
        $this->tree->addBusinessUnitRelation('bu3a', null);
        $this->tree->addBusinessUnitRelation('bu3a1', 'bu3a');
        $this->tree->addBusinessUnitRelation('bu4', null);
        $this->tree->addBusinessUnitRelation('bu41', 'bu4');
        $this->tree->addBusinessUnitRelation('bu411', 'bu41');

        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu2');
        $this->tree->addUser('user3', 'bu3');
        $this->tree->addUser('user31', 'bu31');
        $this->tree->addUser('user4', 'bu4');
        $this->tree->addUser('user41', 'bu41');
        $this->tree->addUser('user411', 'bu411');

        $this->tree->addUserBusinessUnit('user4', 'bu3');
        $this->tree->addUserBusinessUnit('user4', 'bu4');
    }

    public function testIsOrganization()
    {
        $this->assertFalse($this->decisionMaker->isOrganization(null));
        $this->assertFalse($this->decisionMaker->isOrganization('test'));
        $this->assertFalse($this->decisionMaker->isOrganization(new User('')));
        $this->assertTrue($this->decisionMaker->isOrganization(new Organization('')));
        $this->assertTrue(
            $this->decisionMaker->isOrganization(
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization')
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    public function testIsBusinessUnit()
    {
        $this->assertFalse($this->decisionMaker->isBusinessUnit(null));
        $this->assertFalse($this->decisionMaker->isBusinessUnit('test'));
        $this->assertFalse($this->decisionMaker->isBusinessUnit(new User('')));
        $this->assertTrue($this->decisionMaker->isBusinessUnit(new BusinessUnit('')));
        $this->assertTrue(
            $this->decisionMaker->isBusinessUnit(
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit')
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    public function testIsUser()
    {
        $this->assertFalse($this->decisionMaker->isUser(null));
        $this->assertFalse($this->decisionMaker->isUser('test'));
        $this->assertFalse($this->decisionMaker->isUser(new BusinessUnit('')));
        $this->assertTrue($this->decisionMaker->isUser(new User('')));
        $this->assertTrue(
            $this->decisionMaker->isUser(
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User')
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithOrganizationNullUser()
    {
        $this->decisionMaker->isAssociatedWithOrganization(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithOrganizationNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isAssociatedWithOrganization($user, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithBusinessUnitNullUser()
    {
        $this->decisionMaker->isAssociatedWithBusinessUnit(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithBusinessUnitNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isAssociatedWithBusinessUnit($user, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithUserNullUser()
    {
        $this->decisionMaker->isAssociatedWithUser(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsAssociatedWithUserNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isAssociatedWithUser($user, null);
    }

    public function testIsAssociatedWithOrganizationForSystemObject()
    {
        $user = new User('user');
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($user, new \stdClass()));
    }

    public function testIsAssociatedWithBusinessUnitForSystemObject()
    {
        $user = new User('user');
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($user, new \stdClass()));
    }

    public function testIsAssociatedWithUserForSystemObject()
    {
        $user = new User('user');
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($user, new \stdClass()));
    }

    public function testIsAssociatedWithOrganizationForOrganizationObject()
    {
        $this->buildTestTree();

        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $this->org1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $this->org2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $this->org3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user31, $this->org3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->org3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->org4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user411, $this->org4));
    }

    public function testIsAssociatedWithOrganizationForUserObject()
    {
        $this->buildTestTree();

        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $this->user1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $this->user2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user3, $this->user3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user31, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->user4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->user3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $this->user411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user411, $this->user411));
    }

    public function testIsAssociatedWithOrganizationForOrganizationOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->org1);
        $obj2 = new TestEntity(1, $this->org2);
        $obj3 = new TestEntity(1, $this->org3);
        $obj4 = new TestEntity(1, $this->org4);

        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj4));
    }

    public function testIsAssociatedWithOrganizationForBusinessUnitOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->bu1);
        $obj2 = new TestEntity(1, $this->bu2);
        $obj3 = new TestEntity(1, $this->bu3);
        $obj31 = new TestEntity(1, $this->bu31);
        $obj4 = new TestEntity(1, $this->bu4);
        $obj41 = new TestEntity(1, $this->bu41);
        $obj411 = new TestEntity(1, $this->bu411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj41));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj411));
    }

    public function testIsAssociatedWithOrganizationForUserOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->user1);
        $obj2 = new TestEntity(1, $this->user2);
        $obj3 = new TestEntity(1, $this->user3);
        $obj31 = new TestEntity(1, $this->user31);
        $obj4 = new TestEntity(1, $this->user4);
        $obj411 = new TestEntity(1, $this->user411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user3, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithOrganization($this->user4, $obj411));
    }

    public function testIsAssociatedWithBusinessUnitForOrganizationObject()
    {
        $this->buildTestTree();

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $this->org1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $this->org2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->org3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->org3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->org4));
    }

    public function testIsAssociatedWithBusinessUnitForBusinessUnitObject()
    {
        $this->buildTestTree();

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $this->bu1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $this->bu2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->bu3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu41));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu41, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->bu411, true));
    }

    public function testIsAssociatedWithBusinessUnitForUserObject()
    {
        $this->buildTestTree();

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $this->user1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $this->user2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->user3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->user3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->user31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $this->user31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user31, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user31, $this->user31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user4, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $this->user411, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user411, $this->user411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user411, $this->user411, true));
    }

    public function testIsAssociatedWithBusinessUnitForOrganizationOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->org1);
        $obj2 = new TestEntity(1, $this->org2);
        $obj3 = new TestEntity(1, $this->org3);
        $obj4 = new TestEntity(1, $this->org4);

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj4));
    }

    public function testIsAssociatedWithBusinessUnitForBusinessUnitOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->bu1);
        $obj2 = new TestEntity(1, $this->bu2);
        $obj3 = new TestEntity(1, $this->bu3);
        $obj31 = new TestEntity(1, $this->bu31);
        $obj4 = new TestEntity(1, $this->bu4);
        $obj41 = new TestEntity(1, $this->bu41);
        $obj411 = new TestEntity(1, $this->bu411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj41));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj41, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj411, true));
    }

    public function testIsAssociatedWithBusinessUnitForUserOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->user1);
        $obj2 = new TestEntity(1, $this->user2);
        $obj3 = new TestEntity(1, $this->user3);
        $obj31 = new TestEntity(1, $this->user31);
        $obj4 = new TestEntity(1, $this->user4);
        $obj411 = new TestEntity(1, $this->user411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user3, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->user4, $obj411, true));
    }

    public function testIsAssociatedWithUserForUserObject()
    {
        $this->buildTestTree();

        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user1, $this->user1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user2, $this->user2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user3, $this->user3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user31, $this->user31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user4, $this->user4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $this->user3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $this->user411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user411, $this->user411));
    }

    public function testIsAssociatedWithUserForOrganizationOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->org1);
        $obj2 = new TestEntity(1, $this->org2);
        $obj3 = new TestEntity(1, $this->org3);
        $obj4 = new TestEntity(1, $this->org4);

        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj4));
    }

    public function testIsAssociatedWithUserForBusinessUnitOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->bu1);
        $obj2 = new TestEntity(1, $this->bu2);
        $obj3 = new TestEntity(1, $this->bu3);
        $obj31 = new TestEntity(1, $this->bu31);
        $obj4 = new TestEntity(1, $this->bu4);
        $obj41 = new TestEntity(1, $this->bu41);
        $obj411 = new TestEntity(1, $this->bu411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj41));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj411));
    }

    public function testIsAssociatedWithUserForUserOwnedObject()
    {
        $this->buildTestTree();

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $obj = new TestEntity(1);
        $obj1 = new TestEntity(1, $this->user1);
        $obj2 = new TestEntity(1, $this->user2);
        $obj3 = new TestEntity(1, $this->user3);
        $obj31 = new TestEntity(1, $this->user31);
        $obj4 = new TestEntity(1, $this->user4);
        $obj411 = new TestEntity(1, $this->user411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user1, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user1, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user2, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user2, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user3, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user3, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithUser($this->user4, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithUser($this->user4, $obj411));
    }
}
