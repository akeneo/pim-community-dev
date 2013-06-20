<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata;

use Oro\Bundle\DataAuditBundle\Metadata\PropertyMetadata;

class PropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyMetadata
     */
    protected $propertyMetadata;

    public function setUp()
    {
        $this->propertyMetadata = new PropertyMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass',
            'name'
        );
    }

    public function testSerializeUnserialize()
    {
        $this->propertyMetadata->isCollection = true;
        $this->propertyMetadata->method       = 'getName';

        $this->assertEquals($this->propertyMetadata, unserialize(serialize($this->propertyMetadata)));
    }

    public function tearDown()
    {
        $this->propertyMetadata = null;
    }
}
