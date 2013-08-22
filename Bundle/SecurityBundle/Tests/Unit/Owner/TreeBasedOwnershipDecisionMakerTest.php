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
        $user1 = new User('user1');
        $this->tree->addUser('user1', 'org1', null, null);

        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user1, new Organization('org1')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('org2')));
    }

    public function testIsBelongToOrganizationForOrganizationOwnedObject()
    {
        $user1 = new User('user1');
        $user2 = new User('user2');
        $bu = new BusinessUnit('bu');

        $this->tree->addUser('user1', 'org1', null, null);
        $this->tree->addUser('user2', 'org2', 'bu', null);
        $this->tree->addBusinessUnit('bu', 'org2');

        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );

        $this->assertTrue($this->decisionMaker->isBelongToOrganization($user1, new Organization('org1')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new Organization('org2')));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1)));
        $this->assertFalse($this->decisionMaker->isBelongToOrganization($user1, new TestObject(1, $bu)));
        //$this->assertTrue($this->decisionMaker->isBelongToOrganization($user2, new TestObject(1, $bu)));
    }
}
