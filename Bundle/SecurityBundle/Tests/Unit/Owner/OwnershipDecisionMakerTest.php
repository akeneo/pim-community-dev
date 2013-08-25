<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\OwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\BusinessUnit;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\User;

class OwnershipDecisionMakerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnerTree
     */
    private $tree;

    /**
     * @var OwnershipMetadataProvider
     */
    private $metadataProvider;

    /**
     * @var OwnershipDecisionMaker
     */
    private $decisionMaker;

    protected function setUp()
    {
        $this->tree = new OwnerTree();
        $this->metadataProvider = new OwnershipMetadataProvider();
        $classAccessor = new ObjectClassAccessor();
        $this->decisionMaker = new OwnershipDecisionMaker(
            $this->tree,
            $classAccessor,
            new ObjectIdAccessor(),
            new ObjectOwnerAccessor($classAccessor, $this->metadataProvider),
            $this->metadataProvider
        );

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
    }

    public function testIsOrganization()
    {
        $this->assertFalse($this->decisionMaker->isOrganization(null));
        $this->assertFalse($this->decisionMaker->isOrganization('test'));
        $this->assertFalse($this->decisionMaker->isOrganization(new User('')));
        $this->assertTrue($this->decisionMaker->isOrganization(new Organization('')));
        $this->assertTrue(
            $this->decisionMaker->isOrganization(
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Organization')
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
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\BusinessUnit')
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
                $this->getMockBuilder('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\User')
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToOrganizationNullUser()
    {
        $this->decisionMaker->isBelongToOrganization(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToOrganizationNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToOrganization($user, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToBusinessUnitNullUser()
    {
        $this->decisionMaker->isBelongToBusinessUnit(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToBusinessUnitNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToBusinessUnit($user, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToUserNullUser()
    {
        $this->decisionMaker->isBelongToUser(null, null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testIsBelongToUserNullObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToUser($user, null);
    }

    public function testIsBelongToOrganizationForSystemObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToOrganization($user, new \stdClass());
    }

    public function testIsBelongToBusinessUnitForSystemObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToBusinessUnit($user, new \stdClass());
    }

    public function testIsBelongToUserForSystemObject()
    {
        $user = new User('user');
        $this->decisionMaker->isBelongToUser($user, new \stdClass());
    }

    public function testIsBelongToOrganizationForOrganizationObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('org')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('anotherOrg')));

        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new Organization('org')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new Organization('anotherOrg')));
    }

    public function testIsBelongToOrganizationForOrganizationOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $org = new Organization('org');
        $anotherOrg = new Organization('anotherOrg');

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $org)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherOrg)));

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $org)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherOrg)));
    }

    public function testIsBelongToOrganizationForBusinessUnitOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );


        $bu = new BusinessUnit('bu');
        $user = new User('user', $bu);
        $this->tree->addUser($user->getId(), $bu->getId());
        $anotherBu = new BusinessUnit('anotherBu');
        $this->tree->addBusinessUnit($anotherBu->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherBu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $bu));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $anotherBu));

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherBu)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $bu));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, $anotherBu));
    }

    public function testIsBelongToOrganizationForUserOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $bu = new BusinessUnit('bu');
        $user = new User('user', $bu);
        $this->tree->addUser($user->getId(), $bu->getId());
        $anotherUser = new User('anotherUser');
        $this->tree->addUser($anotherUser->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherUser)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $anotherUser));

        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherUser)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, $anotherUser));
    }

    public function testIsBelongToBusinessUnitForBusinessUnitObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new BusinessUnit('bu')));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new BusinessUnit('anotherBu')));

        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new BusinessUnit('bu')));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new BusinessUnit('anotherBu')));
    }

    public function testIsBelongToBusinessUnitForOrganizationOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');

        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $org = new Organization('org');

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $org)));
    }

    public function testIsBelongToBusinessUnitForBusinessUnitOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );


        $bu = new BusinessUnit('bu');
        $user = new User('user', $bu);
        $this->tree->addUser($user->getId(), $bu->getId());
        $anotherBu = new BusinessUnit('anotherBu');
        $this->tree->addBusinessUnit($anotherBu->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $anotherBu)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, $bu));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, $anotherBu));

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $anotherBu)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, $bu));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, $anotherBu));
    }

    public function testIsBelongToBusinessUnitForUserOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $bu = new BusinessUnit('bu');
        $user = new User('user', $bu);
        $this->tree->addUser($user->getId(), $bu->getId());
        $anotherUser = new User('anotherUser');
        $this->tree->addUser($anotherUser->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $anotherUser)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, $anotherUser));

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $anotherUser)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, $anotherUser));
    }

    public function testIsBelongToBusinessUnitOwningDeep()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $user3 = new User('user3');
        $this->tree->addUser('user1', 'bu1');
        $this->tree->addUser('user2', 'bu2');
        $this->tree->addUser('user3', 'bu3');
        $bu1 = new BusinessUnit('bu1');
        $bu2 = new BusinessUnit('bu2');
        $bu3 = new BusinessUnit('bu3');
        $this->tree->addBusinessUnit('bu1', 'org');
        $this->tree->addBusinessUnit('bu2', 'org');
        $this->tree->addBusinessUnit('bu3', 'org');
        $this->tree->addBusinessUnitRelation('bu1', null);
        $this->tree->addBusinessUnitRelation('bu2', 'bu1');
        $this->tree->addBusinessUnitRelation('bu3', 'bu2');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1), true));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu1)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu1), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu2)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu2), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu3)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu3), true));

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu1), true));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu2)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu2), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu3)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user2, new TestObject(1, $bu3), true));

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu1), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu2)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu2), true));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu3)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user3, new TestObject(1, $bu3), true));
    }

    public function testIsBelongToBusinessUnitAssigningDeep()
    {
        $user1 = new User('user1');
        $this->tree->addUser('user1', null);
        $this->tree->addUserBusinessUnit('user1', 'bu2');
        $bu1 = new BusinessUnit('bu1');
        $bu2 = new BusinessUnit('bu2');
        $bu3 = new BusinessUnit('bu3');
        $this->tree->addBusinessUnit('bu1', 'org');
        $this->tree->addBusinessUnit('bu2', 'org');
        $this->tree->addBusinessUnit('bu3', 'org');
        $this->tree->addBusinessUnitRelation('bu1', null);
        $this->tree->addBusinessUnitRelation('bu2', 'bu1');
        $this->tree->addBusinessUnitRelation('bu3', 'bu2');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu1)));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu1), true));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu2)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu2), true));
        $this->assertFalse($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu3)));
        $this->assertTrue($this->decisionMaker->isBelongToBusinessUnit($user1, new TestObject(1, $bu3), true));
    }

    public function testIsBelongToUserForUserObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');

        $this->assertTrue($this->decisionMaker->isBelongToUser($user1, new User('user1')));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new User('anotherUser')));

        $this->assertTrue($this->decisionMaker->isBelongToUser($user2, new User('user2')));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, new User('anotherUser')));
    }

    public function testIsBelongToUserForOrganizationOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');

        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $org = new Organization('org');

        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, new TestObject(1, $org)));
    }

    public function testIsBelongToUserForBusinessUnitOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );


        $bu = new BusinessUnit('bu');
        $user = new User('user', $bu);
        $this->tree->addUser($user->getId(), $bu->getId());
        $anotherBu = new BusinessUnit('anotherBu');
        $this->tree->addBusinessUnit($anotherBu->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1, $anotherBu)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, $bu));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, $anotherBu));

        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, new TestObject(1, $anotherBu)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, $bu));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user2, $anotherBu));
    }

    public function testIsBelongToUserForUserOwnedObject()
    {
        $user1 = new User('user1');
        $this->tree->addUser('user1', null);

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $anotherUser = new User('anotherUser');
        $this->tree->addUser($anotherUser->getId(), null);

        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, new TestObject(1, $anotherUser)));
        $this->assertFalse($this->decisionMaker->isBelongToUser($user1, $anotherUser));
        $this->assertTrue($this->decisionMaker->isBelongToUser($user1, new TestObject(1, $user1)));
    }
}
