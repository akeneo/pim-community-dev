<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Permission;

use Oro\Bundle\SecurityBundle\Acl\Metadata\OwnershipMetadata;

class OwnershipMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithoutParameters()
    {
        $metadata = new OwnershipMetadata();
        $this->assertFalse($metadata->hasOwner());
        $this->assertFalse($metadata->isOrganizationOwned());
        $this->assertFalse($metadata->isBusinessUnitOwned());
        $this->assertFalse($metadata->isUserOwned());
        $this->assertEquals('', $metadata->getOwnerIdColumnName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithInvalidOwnershipType()
    {
        new OwnershipMetadata('test');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithoutOwnerIdColumnName()
    {
        new OwnershipMetadata('organization');
    }

    public function testOrganizationOwnership()
    {
        $metadata = new OwnershipMetadata('organization', 'org_id');
        $this->assertTrue($metadata->hasOwner());
        $this->assertTrue($metadata->isOrganizationOwned());
        $this->assertFalse($metadata->isBusinessUnitOwned());
        $this->assertFalse($metadata->isUserOwned());
        $this->assertEquals('org_id', $metadata->getOwnerIdColumnName());
    }

    public function testBusinessUnitOwnership()
    {
        $metadata = new OwnershipMetadata('business_unit', 'bu_id');
        $this->assertTrue($metadata->hasOwner());
        $this->assertFalse($metadata->isOrganizationOwned());
        $this->assertTrue($metadata->isBusinessUnitOwned());
        $this->assertFalse($metadata->isUserOwned());
        $this->assertEquals('bu_id', $metadata->getOwnerIdColumnName());
    }

    public function testUserOwnership()
    {
        $metadata = new OwnershipMetadata('user', 'user_id');
        $this->assertTrue($metadata->hasOwner());
        $this->assertFalse($metadata->isOrganizationOwned());
        $this->assertFalse($metadata->isBusinessUnitOwned());
        $this->assertTrue($metadata->isUserOwned());
        $this->assertEquals('user_id', $metadata->getOwnerIdColumnName());
    }

    public function testSerialization()
    {
        $metadata = new OwnershipMetadata('organization', 'org_id');
        $data = $metadata->serialize();
        $metadata = new OwnershipMetadata();
        $this->assertFalse($metadata->isOrganizationOwned());
        $this->assertEquals('', $metadata->getOwnerIdColumnName());
        $metadata->unserialize($data);
        $this->assertTrue($metadata->isOrganizationOwned());
        $this->assertEquals('org_id', $metadata->getOwnerIdColumnName());
    }
}
