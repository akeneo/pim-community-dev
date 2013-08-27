<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config\Id;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

class EntityIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityConfigId
     */
    protected $entityId;

    public function setUp()
    {
        $this->entityId = new EntityConfigId('Test\Class', 'testScope');
    }

    public function testGetConfig()
    {
        $this->assertEquals('Test\Class', $this->entityId->getClassName());
        $this->assertEquals('testScope', $this->entityId->getScope());
        $this->assertEquals('entity_testScope_Test-Class', $this->entityId->toString());
    }

    public function testSerialize()
    {
        $this->assertEquals($this->entityId, unserialize(serialize($this->entityId)));
    }
}
