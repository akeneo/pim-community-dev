<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Entity;

use Oro\Bundle\SecurityBundle\Entity\SecurityMetadata;

class SecurityMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityMetadata */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new SecurityMetadata('SomeType', 'SomeClass', 'SomeGroup');
    }

    public function testGetters()
    {
        $this->assertEquals('SomeType', $this->entity->getType());
        $this->assertEquals('SomeClass', $this->entity->getClassName());
        $this->assertEquals('SomeGroup', $this->entity->getGroup());
    }

    public function testSerialize()
    {
        $data = $this->entity->serialize();
        $emptyEntity = new SecurityMetadata();
        $emptyEntity->unserialize($data);
        $this->assertEquals('SomeType', $emptyEntity->getType());
        $this->assertEquals('SomeClass', $emptyEntity->getClassName());
        $this->assertEquals('SomeGroup', $emptyEntity->getGroup());
    }
}
