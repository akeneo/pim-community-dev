<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker\Condition;

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAssociationCondition;

class JoinAssociationConditionTest extends \PHPUnit_Framework_TestCase
{
    public function testJoinAssociationCondition()
    {
        $condition = new JoinAssociationCondition('test', 'id', [], '', []);
        $condition->setEntityClass('testClass');
        $condition->setJoinConditions([1]);
        $this->assertEquals('testClass', $condition->getEntityClass());
        $this->assertEquals([1], $condition->getJoinConditions());
    }
}
