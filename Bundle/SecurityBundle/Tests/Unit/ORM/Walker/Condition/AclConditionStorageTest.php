<?php
namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker\Condition;

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclConditionStorage;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\SubRequestAclConditionStorage;

class AclConditionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $whereConditions = [new AclCondition('test', 'id', [])];
        $joinConditions = [new JoinAclCondition('testJoin', 'id', [])];
        $subRequests = [new SubRequestAclConditionStorage([], [])];
        $storage = new AclConditionStorage([], []);

        $this->assertTrue($storage->isEmpty());
        $storage->setJoinConditions($joinConditions);
        $this->assertFalse($storage->isEmpty());
        $storage->setJoinConditions([]);
        $storage->setWhereConditions($whereConditions);
        $this->assertFalse($storage->isEmpty());
        $storage->setWhereConditions([]);
        $storage->setSubRequests($subRequests);
        $this->assertFalse($storage->isEmpty());
        $storage->setWhereConditions($whereConditions);
        $storage->setJoinConditions($joinConditions);
        $this->assertEquals($whereConditions, $storage->getWhereConditions());
        $this->assertEquals($joinConditions, $storage->getJoinConditions());
        $this->assertEquals($subRequests, $storage->getSubRequests());
    }
}
