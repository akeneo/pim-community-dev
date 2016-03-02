<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

class AccessLevelTest extends \PHPUnit_Framework_TestCase
{
    public function testConstantValues()
    {
        $this->assertEquals(-1, AccessLevel::UNKNOWN);
        $this->assertEquals(0, AccessLevel::NONE_LEVEL);
        $this->assertGreaterThan(AccessLevel::NONE_LEVEL, AccessLevel::BASIC_LEVEL);
        $this->assertGreaterThan(AccessLevel::BASIC_LEVEL, AccessLevel::LOCAL_LEVEL);
        $this->assertGreaterThan(AccessLevel::LOCAL_LEVEL, AccessLevel::DEEP_LEVEL);
        $this->assertGreaterThan(AccessLevel::DEEP_LEVEL, AccessLevel::GLOBAL_LEVEL);
        $this->assertGreaterThan(AccessLevel::GLOBAL_LEVEL, AccessLevel::SYSTEM_LEVEL);
    }

    public function testGetConst()
    {
        $this->assertEquals(AccessLevel::NONE_LEVEL, AccessLevel::getConst('NONE_LEVEL'));
        $this->assertEquals(AccessLevel::BASIC_LEVEL, AccessLevel::getConst('BASIC_LEVEL'));
        $this->assertEquals(AccessLevel::LOCAL_LEVEL, AccessLevel::getConst('LOCAL_LEVEL'));
        $this->assertEquals(AccessLevel::DEEP_LEVEL, AccessLevel::getConst('DEEP_LEVEL'));
        $this->assertEquals(AccessLevel::GLOBAL_LEVEL, AccessLevel::getConst('GLOBAL_LEVEL'));
        $this->assertEquals(AccessLevel::SYSTEM_LEVEL, AccessLevel::getConst('SYSTEM_LEVEL'));
    }

    public function testAllAccessLevelNames()
    {
        $this->assertEquals(['BASIC', 'LOCAL', 'DEEP', 'GLOBAL', 'SYSTEM'], AccessLevel::$allAccessLevelNames);
    }
}
