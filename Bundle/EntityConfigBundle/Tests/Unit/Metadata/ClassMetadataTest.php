<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityMetadata
     */
    protected $classMetadata;

    public function setUp()
    {
        $this->classMetadata       = new EntityMetadata(DemoEntity::ENTITY_NAME);
        $this->classMetadata->mode = ConfigModelManager::MODE_DEFAULT;
    }

    public function testSerialize()
    {
        $this->assertEquals($this->classMetadata, unserialize(serialize($this->classMetadata)));
    }

    public function testMerge()
    {
        $newMetadata       = new EntityMetadata(DemoEntity::ENTITY_NAME);
        $newMetadata->mode = ConfigModelManager::MODE_READONLY;
        $this->classMetadata->merge($newMetadata);

        $this->assertEquals(ConfigModelManager::MODE_READONLY, $this->classMetadata->mode);
    }
}
