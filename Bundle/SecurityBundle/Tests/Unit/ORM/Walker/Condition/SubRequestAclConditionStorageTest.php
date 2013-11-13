<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker\Condition;


use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\SubRequestAclConditionStorage;

class SubRequestAclConditionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testSubRequestAclConditionStorage()
    {
        $storage = new SubRequestAclConditionStorage(
            [new AclCondition('test', 'id', [2])],
            [new JoinAclCondition('testClass', 'owner', [1])]
        );
        $this->assertFalse($storage->isEmpty());
        $storage->setFactorId(1);
        $this->assertEquals(1, $storage->getFactorId());
        $storage->setWhereConditions([]);
        $this->assertFalse($storage->isEmpty());
        $storage->setJoinConditions([]);
        $this->assertTrue($storage->isEmpty());
    }
}
