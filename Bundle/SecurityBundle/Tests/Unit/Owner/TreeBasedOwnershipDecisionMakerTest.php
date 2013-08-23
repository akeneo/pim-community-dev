<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\TreeBasedOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\BusinessUnit;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\User;

class TreeBasedOwnershipDecisionMakerTest extends \PHPUnit_Framework_TestCase
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
     * @var TreeBasedOwnershipDecisionMaker
     */
    private $decisionMaker;

    protected function setUp()
    {
        $this->tree = new OwnerTree();
        $this->metadataProvider = new OwnershipMetadataProvider();
        $classAccessor = new ObjectClassAccessor();
        $this->decisionMaker = new TreeBasedOwnershipDecisionMaker(
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

    public function testIsBelongToOrganizationForOrganizationObject()
    {
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $user1 = new User('user1');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('org')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('anotherOrg')));

        $user2 = new User('user2');
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new Organization('org')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new Organization('anotherOrg')));
    }

    public function testIsBelongToOrganizationForOrganizationOwnedObject()
    {
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $org = new Organization('org');
        $anotherOrg = new Organization('anotherOrg');

        $user1 = new User('user1');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $org)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherOrg)));

        $user2 = new User('user2');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $org)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherOrg)));
    }

    public function testIsBelongToOrganizationForBusinessUnitOwnedObject()
    {
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );


        $bu = new BusinessUnit('bu', new Organization('org'));
        $user = new User('user', $bu);
        $anotherBu = new BusinessUnit('anotherBu');

        $user1 = new User('user1');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherBu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $bu));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $anotherBu));

        $user2 = new User('user2');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $bu)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherBu)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $bu));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, $anotherBu));
    }

    public function testIsBelongToOrganizationForUserOwnedObject()
    {
        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu');
        $this->tree->addBusinessUnit('bu', 'org');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );

        $bu = new BusinessUnit('bu', new Organization('org'));
        $user = new User('user', $bu);
        $anotherUser = new User('anotherUser');

        $user1 = new User('user1');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $anotherUser)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, $anotherUser));

        $user2 = new User('user2');
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $user)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $anotherUser)));
        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, $user));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user2, $anotherUser));
    }
}
