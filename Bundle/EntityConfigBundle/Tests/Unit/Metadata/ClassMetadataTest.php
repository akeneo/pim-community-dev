<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata;

use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityMetadata
     */
    protected $classMetadata;

    public function setUp()
    {
        $this->classMetadata = new EntityMetadata(ConfigManagerTest::DEMO_ENTITY);
        $this->classMetadata->mode = 'hidden';
    }

    public function testSerialize()
    {
        $this->assertEquals($this->classMetadata, unserialize(serialize($this->classMetadata)));
    }

    public function testMerge()
    {
        $newMetadata = new EntityMetadata(ConfigManagerTest::DEMO_ENTITY);
        $newMetadata->mode = 'readonly';
        $this->classMetadata->merge($newMetadata);

        $this->assertEquals('readonly', $this->classMetadata->mode);
    }
}
