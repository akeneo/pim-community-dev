<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata;

use Oro\Bundle\EntityConfigBundle\Metadata\ClassMetadata;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    protected $classMetadata;

    public function setUp()
    {
        $this->classMetadata = new ClassMetadata(ConfigManagerTest::DEMO_ENTITY);
    }

    public function testSerialize()
    {
        $this->assertEquals($this->classMetadata, unserialize(serialize($this->classMetadata)));
    }
}
