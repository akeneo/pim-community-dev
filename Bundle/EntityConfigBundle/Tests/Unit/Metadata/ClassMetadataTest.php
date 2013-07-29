<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata;

use Oro\Bundle\EntityConfigBundle\Metadata\ConfigClassMetadata;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigClassMetadata
     */
    protected $classMetadata;

    public function setUp()
    {
        $this->classMetadata = new ConfigClassMetadata(ConfigManagerTest::DEMO_ENTITY);
        $this->classMetadata->viewMode = 'hidden';
    }

    public function testSerialize()
    {
        $this->assertEquals($this->classMetadata, unserialize(serialize($this->classMetadata)));
    }

    public function testMerge()
    {
        $newMetadata = new ConfigClassMetadata(ConfigManagerTest::DEMO_ENTITY);
        $newMetadata->viewMode = 'readonly';
        $this->classMetadata->merge($newMetadata);

        $this->assertEquals('readonly', $this->classMetadata->viewMode);
    }
}
