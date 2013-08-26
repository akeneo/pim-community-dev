<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipMaskBuilder;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;

class OwnershipAclExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnershipAclExtension
     */
    private $extension;

    /** @var OwnershipMetadataProvider */
    private $metadataProvider;

    protected function setUp()
    {
        $this->metadataProvider = new OwnershipMetadataProvider();
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
        $this->extension = TestHelper::get($this)->createOwnershipAclExtension($this->metadataProvider);
    }

    /**
     * @dataProvider validateMaskForOrganizationProvider
     */
    public function testValidateMaskForOrganization($mask)
    {
        $this->extension->validateMask($mask, new Organization());
    }

    /**
     * @dataProvider validateMaskForOrganizationInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForOrganizationInvalid($mask)
    {
        $this->extension->validateMask($mask, new Organization());
    }

    /**
     * @dataProvider validateMaskForBusinessUnitProvider
     */
    public function testValidateMaskForBusinessUnit($mask)
    {
        $this->extension->validateMask($mask, new BusinessUnit());
    }

    /**
     * @dataProvider validateMaskForBusinessUnitInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForBusinessUnitInvalid($mask)
    {
        $this->extension->validateMask($mask, new BusinessUnit());
    }

    /**
     * @dataProvider validateMaskForUserProvider
     */
    public function testValidateMaskForUser($mask)
    {
        $this->extension->validateMask($mask, new User());
    }

    /**
     * @dataProvider validateMaskForUserInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForUserInvalid($mask)
    {
        $this->extension->validateMask($mask, new User());
    }

    /**
     * @dataProvider validateMaskForOrganizationOwnedProvider
     */
    public function testValidateMaskForOrganizationOwned($mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );
        $this->extension->validateMask($mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForOrganizationOwnedInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForOrganizationOwnedInvalid($mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id')
        );
        $this->extension->validateMask($mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForUserOwnedProvider
     */
    public function testValidateMaskForUserOwned($mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );
        $this->extension->validateMask($mask, new TestEntity());
    }

    /**
     * @dataProvider validateMaskForUserOwnedInvalidProvider
     * @expectedException \Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException
     */
    public function testValidateMaskForUserOwnedInvalid($mask)
    {
        $this->metadataProvider->setMetadata(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
            new OwnershipMetadata('USER', 'owner', 'owner_id')
        );
        $this->extension->validateMask($mask, new TestEntity());
    }

    public static function validateMaskForOrganizationProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
        );
    }

    public static function validateMaskForOrganizationInvalidProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
        );
    }

    public static function validateMaskForBusinessUnitProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array(OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array(OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array(OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array(OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array(OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array(OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array(OwnershipMaskBuilder::MASK_SHARE_LOCAL),
        );
    }

    public static function validateMaskForBusinessUnitInvalidProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM | OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL | OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP | OwnershipMaskBuilder::MASK_VIEW_LOCAL),
        );
    }

    public static function validateMaskForUserProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array(OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array(OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array(OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array(OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array(OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array(OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array(OwnershipMaskBuilder::MASK_SHARE_LOCAL),
        );
    }

    public static function validateMaskForUserInvalidProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM | OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL | OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP | OwnershipMaskBuilder::MASK_VIEW_LOCAL),
        );
    }

    public static function validateMaskForUserOwnedProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array(OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array(OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array(OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array(OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array(OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array(OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array(OwnershipMaskBuilder::MASK_SHARE_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_CREATE_BASIC),
            array(OwnershipMaskBuilder::MASK_EDIT_BASIC),
            array(OwnershipMaskBuilder::MASK_DELETE_BASIC),
            array(OwnershipMaskBuilder::MASK_ASSIGN_BASIC),
            array(OwnershipMaskBuilder::MASK_SHARE_BASIC),
        );
    }

    public static function validateMaskForUserOwnedInvalidProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM | OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL | OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP | OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL | OwnershipMaskBuilder::MASK_VIEW_BASIC),
        );
    }

    public static function validateMaskForOrganizationOwnedProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
        );
    }

    public static function validateMaskForOrganizationOwnedInvalidProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM | OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL | OwnershipMaskBuilder::MASK_VIEW_DEEP),
        );
    }
}
