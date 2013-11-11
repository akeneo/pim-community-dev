<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition;

class AbstractConditionTest extends \PHPUnit_Framework_TestCase
{
    public function testMessages()
    {
        /** @var AbstractCondition $condition */
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition')
            ->getMockForAbstractClass();
        $this->assertSame($condition, $condition->setMessage('Test'));
        $this->assertEquals('Test', $condition->getMessage());
    }

    /**
     * @param bool $allowed
     * @param bool $message
     * @param bool $expectMessage
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($allowed, $message, $expectMessage = false)
    {
        $errorMessage = 'Some error message';
        $context = array('key' => 'value');

        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition')
            ->setMethods(array('isConditionAllowed'))
            ->getMockForAbstractClass();
        $condition->expects($this->any())
            ->method('isConditionAllowed')
            ->with($context)
            ->will($this->returnValue($allowed));

        /** @var $condition AbstractCondition */
        if ($message) {
            $condition->setMessage($errorMessage);
        }

        // without message collection
        $this->assertEquals($allowed, $condition->isAllowed($context));

        // with message collection
        $errors = new ArrayCollection();
        $this->assertEquals($allowed, $condition->isAllowed($context, $errors));
        if ($expectMessage) {
            $this->assertEquals(array($errorMessage), $errors->getValues());
        } else {
            $this->assertEmpty($errors->getValues());
        }
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return array(
            'allowed, no error message' => array(
                'allowed' => true,
                'message' => false,
            ),
            'not allowed, no error message' => array(
                'allowed' => false,
                'message' => false,
            ),
            'allowed, with error message' => array(
                'allowed' => true,
                'message' => true,
            ),
            'not allowed, with error message' => array(
                'allowed' => false,
                'message' => true,
                'expectMessage' => true,
            ),
        );
    }

    public function testIsConditionAllowedDefault()
    {
        /** @var AbstractCondition $condition */
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition')
            ->getMockForAbstractClass();
        $this->assertFalse($condition->isAllowed('anything'));
    }
}
