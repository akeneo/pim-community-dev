<?php
namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker\Condition;

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAclCondition;

class JoinAclConditionTest extends \PHPUnit_Framework_TestCase
{
    public function testJoinAclCondition()
    {
        $condition = new JoinAclCondition('test', 'id', []);
        $condition->setFromKey(1);
        $condition->setJoinKey(2);
        $this->assertEquals(1, $condition->getFromKey());
        $this->assertEquals(2, $condition->getJoinKey());
    }
}
