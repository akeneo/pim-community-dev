<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObject;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestObjectWithOwnerFieldButWithoutGetOwnerMethod;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class ObjectOwnerAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOwner()
    {
        $metadataProvider = new OwnershipMetadataProvider();
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), $metadataProvider);

        $obj1 = new TestObject('testId1');
        $obj1->setOwner('testOwner1');
        $metadataProvider->setMetadata(get_class($obj1), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));
        $this->assertEquals('testOwner1', $accessor->getOwner($obj1));

        $obj2 = new TestObjectWithOwnerFieldButWithoutGetOwnerMethod('testOwner2');
        $metadataProvider->setMetadata(get_class($obj2), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));
        $this->assertEquals('testOwner2', $accessor->getOwner($obj2));
    }

    public function testGetOwnerNoMetadata()
    {
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), new OwnershipMetadataProvider());

        $obj = new TestObject('testId');
        $obj->setOwner('testOwner');
        $this->assertNull($accessor->getOwner($obj));
    }
}
