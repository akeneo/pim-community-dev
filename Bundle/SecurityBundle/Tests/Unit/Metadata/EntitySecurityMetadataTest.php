<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Metadata;

use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadata;

class EntitySecurityMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntitySecurityMetadata */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new EntitySecurityMetadata('SomeType', 'SomeClass', 'SomeGroup', 'SomeLabel');
    }

    public function testGetters()
    {
        $this->assertEquals('SomeType', $this->entity->getSecurityType());
        $this->assertEquals('SomeClass', $this->entity->getClassName());
        $this->assertEquals('SomeGroup', $this->entity->getGroup());
        $this->assertEquals('SomeLabel', $this->entity->getLabel());
    }

    public function testSerialize()
    {
        $data = serialize($this->entity);
        $emptyEntity = unserialize($data);
        $this->assertEquals('SomeType', $emptyEntity->getSecurityType());
        $this->assertEquals('SomeClass', $emptyEntity->getClassName());
        $this->assertEquals('SomeGroup', $emptyEntity->getGroup());
        $this->assertEquals('SomeLabel', $this->entity->getLabel());
    }
}
