<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProviderStub;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class EntityAclExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityAclExtension
     */
    private $extension;

    /** @var OwnershipMetadataProviderStub */
    private $metadataProvider;

    /**
     * @var OwnerTree
     */
    private $tree;

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

        $this->extension = TestHelper::get($this)->createEntityAclExtension($this->metadataProvider, $this->tree);
    }

    private function buildTestTree()
    {
        /**
         * org1  org2  org3       org4
         *             |          |
         * bu1   bu2   bu3        bu4
         *       |     |          |
         *       |     +-bu31     |
         *       |     | |        |
         *       |     | +-user31 |
         *       |     |          |
         * user1 user2 user3      user4
         *                        |
         *                        +-bu3
         *                        +-bu4
         *                          |
         *                          +-bu41
         *                            |
         *                            +-bu411
         *                              |
         *                              +-user411
         */
        $this->tree->addBusinessUnit('bu1', null);
        $this->tree->addBusinessUnit('bu2', null);
        $this->tree->addBusinessUnit('bu3', 'org3');
        $this->tree->addBusinessUnit('bu31', 'org3');
        $this->tree->addBusinessUnit('bu4', 'org4');
        $this->tree->addBusinessUnit('bu41', 'org4');
        $this->tree->addBusinessUnit('bu411', 'org4');

        $this->tree->addBusinessUnitRelation('bu1', null);
        $this->tree->addBusinessUnitRelation('bu2', null);
        $this->tree->addBusinessUnitRelation('bu3', null);
        $this->tree->addBusinessUnitRelation('bu31', 'bu3');
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

    /**
     * @dataProvider validateMaskForOrganizationProvider
     */
    public function testValidateMaskForOrganization($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new Organization());
    }

    /**
     * @dataProvider validateMaskForOrganizationInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForOrganizationInvalid($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new Organization());
    }

    /**
     * @dataProvider validateMaskForBusinessUnitProvider
     */
    public function testValidateMaskForBusinessUnit($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new BusinessUnit());
    }

    /**
     * @dataProvider validateMaskForBusinessUnitInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForBusinessUnitInvalid($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new BusinessUnit());
    }

    /**
     * @dataProvider validateMaskForUserProvider
     */
    public function testValidateMaskForUser($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new User());
    }

    /**
     * @dataProvider validateMaskForUserInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForUserInvalid($permission, $mask)
    {
        $this->extension->validateMask($permission, $mask, new User());
    }

    /**
     * @dataProvider validateMaskForOrganizationOwnedProvider
     */
    public function testValidateMaskForOrganizationOwned($permission, $mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );
        $this->extension->validateMask($permission, $mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForOrganizationOwnedInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForOrganizationOwnedInvalid($permission, $mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );
        $this->extension->validateMask($permission, $mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForUserOwnedProvider
     */
    public function testValidateMaskForUserOwned($permission, $mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );
        $this->extension->validateMask($permission, $mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForUserOwnedInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForUserOwnedInvalid($permission, $mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );
        $this->extension->validateMask($permission, $mask, new TestEntity());
    }

    /**
     * @dataProvider decideIsGrantingProvider
     */
    public function testDecideIsGranting($triggeredMask, $user, $object, $expectedResult)
    {
        $this->buildTestTree();

        if ($object instanceof TestEntity && $object->getOwner() !== null) {
            $owner = $object->getOwner();
            if (is_a($owner, $this->metadataProvider->getOrganizationClass())) {
                $this->metadataProvider->setMetadata(
                    get_class($object),
                    new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
                );
            } elseif (is_a($owner, $this->metadataProvider->getBusinessUnitClass())) {
                $this->metadataProvider->setMetadata(
                    get_class($object),
                    new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
                );
            } elseif (is_a($owner, $this->metadataProvider->getUserClass())) {
                $this->metadataProvider->setMetadata(
                    get_class($object),
                    new OwnershipMetadata('USER', 'owner', 'owner_id')
                );
            }
        }

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->assertEquals(
            $expectedResult,
            $this->extension->decideIsGranting($triggeredMask, $object, $token)
        );
    }

    public function decideIsGrantingProvider()
    {
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

        return array(
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM, null, null, true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, null, null, true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, null, null, true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, null, null, true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, null, null, true),
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM, null, 'foo', true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, null, 'foo', true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, null, 'foo', true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, null, 'foo', true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, null, 'foo', true),
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM, null, new ObjectIdentity('test', 'foo'), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, null, new ObjectIdentity('test', 'foo'), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, null, new ObjectIdentity('test', 'foo'), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, null, new ObjectIdentity('test', 'foo'), true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, null, new ObjectIdentity('test', 'foo'), true),
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM, null, new TestEntity(1), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, null, new TestEntity(1), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, null, new TestEntity(1), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, null, new TestEntity(1), true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, null, new TestEntity(1), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user3, new TestEntity(1, $this->org3), false),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->org4), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user3, new TestEntity(1, $this->bu3), false),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->bu4), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->bu411), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user3, new TestEntity(1, $this->bu3), false),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user4, new TestEntity(1, $this->bu4), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user4, new TestEntity(1, $this->bu411), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user3, new TestEntity(1, $this->bu3), false),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user4, new TestEntity(1, $this->bu4), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user4, new TestEntity(1, $this->bu411), false),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user3, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->user4), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->user411), true),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, $this->user4, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user3, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user4, new TestEntity(1, $this->user4), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user4, new TestEntity(1, $this->user411), true),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, $this->user4, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user3, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user4, new TestEntity(1, $this->user4), true),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user4, new TestEntity(1, $this->user411), false),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, $this->user4, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, $this->user3, new TestEntity(1, $this->user3), true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, $this->user4, new TestEntity(1, $this->user4), true),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, $this->user4, new TestEntity(1, $this->user411), false),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, $this->user4, new TestEntity(1, $this->user3), false),
        );
    }

    public static function validateMaskForOrganizationProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_SYSTEM),
        );
    }

    public static function validateMaskForOrganizationInvalidProvider()
    {
        return array(
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_BASIC),
        );
    }

    public static function validateMaskForBusinessUnitProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_DEEP),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_DEEP),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_LOCAL),
        );
    }

    public static function validateMaskForBusinessUnitInvalidProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM | EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL | EntityMaskBuilder::MASK_VIEW_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP | EntityMaskBuilder::MASK_VIEW_LOCAL),
        );
    }

    public static function validateMaskForUserProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_DEEP),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_DEEP),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_LOCAL),
        );
    }

    public static function validateMaskForUserInvalidProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM | EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL | EntityMaskBuilder::MASK_VIEW_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP | EntityMaskBuilder::MASK_VIEW_LOCAL),
        );
    }

    public static function validateMaskForUserOwnedProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_DEEP),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_DEEP),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_LOCAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_BASIC),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_BASIC),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_BASIC),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_BASIC),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_BASIC),
        );
    }

    public static function validateMaskForUserOwnedInvalidProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM | EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL | EntityMaskBuilder::MASK_VIEW_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP | EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL | EntityMaskBuilder::MASK_VIEW_BASIC),
        );
    }

    public static function validateMaskForOrganizationOwnedProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE', EntityMaskBuilder::MASK_SHARE_GLOBAL),
        );
    }

    public static function validateMaskForOrganizationOwnedInvalidProvider()
    {
        return array(
            array('VIEW', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_SYSTEM | EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('VIEW', EntityMaskBuilder::MASK_VIEW_GLOBAL | EntityMaskBuilder::MASK_VIEW_DEEP),
        );
    }
}
