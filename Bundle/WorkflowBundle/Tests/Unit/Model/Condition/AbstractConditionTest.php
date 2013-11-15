<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition;

class AbstractConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition')
            ->setMethods(array('isConditionAllowed'))
            ->getMockForAbstractClass();
    }

    public function testMessages()
    {
        $this->assertSame($this->condition, $this->condition->setMessage('Test'));
        $this->assertAttributeEquals('Test', 'message', $this->condition);
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

        $this->condition->expects($this->any())
            ->method('isConditionAllowed')
            ->with($context)
            ->will($this->returnValue($allowed));

        if ($message) {
            $this->condition->setMessage($errorMessage);
        }

        // without message collection
        $this->assertEquals($allowed, $this->condition->isAllowed($context));

        // with message collection
        $errors = new ArrayCollection();
        $this->assertEquals($allowed, $this->condition->isAllowed($context, $errors));
        if ($expectMessage) {
            $this->assertEquals(1, $errors->count());
            $this->assertEquals(
                array('message' => $errorMessage, 'parameters' => array()),
                $errors->get(0)
            );
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

    public function testAddError()
    {
        $context = array('foo' => 'fooValue', 'bar' => 'barValue');
        $options = array('left' => 'foo', 'right' => 'bar');

        $left = $options['left'];
        $right = $options['right'];

        $this->condition->initialize($options);
        $message = 'Error message.';
        $this->condition->setMessage($message);

        $this->condition->expects($this->once())->method('isConditionAllowed')
            ->with($context)
            ->will($this->returnValue(false));

        $errors = new ArrayCollection();

        $this->assertFalse($this->condition->isAllowed($context, $errors));

        $this->assertEquals(1, $errors->count());
        $this->assertEquals(array('message' => $message, 'parameters' => array()), $errors->get(0));
    }
}
