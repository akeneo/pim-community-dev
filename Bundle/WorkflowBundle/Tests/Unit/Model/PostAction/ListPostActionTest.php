<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\ListPostAction;

class ListPostActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListPostAction
     */
    protected $listPostAction;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $postActionBuilder;

    protected function setUp()
    {
        $this->listPostAction = new ListPostAction();
    }

    protected function tearDown()
    {
        unset($this->listPostAction);
    }

    public function testAddPostAction()
    {
        $expectedPostActions = array();
        for ($i = 0; $i < 3; $i++) {
            $postAction = $this->getPostActionMock();
            $breakOnFailure = (bool)$i%2;
            $this->listPostAction->addPostAction($postAction, $breakOnFailure);
            $expectedPostActions[] = array(
                'instance' => $postAction,
                'breakOnFailure' => $breakOnFailure
            );
        }

        $this->assertAttributeEquals($expectedPostActions, 'postActions', $this->listPostAction);
    }

    public function testExecute()
    {
        $context = array(1, 2, 3);

        for ($i = 0; $i < 3; $i++) {
            $postAction = $this->getPostActionMock();
            $postAction->expects($this->once())
                ->method('execute')
                ->with($context);
            $this->listPostAction->addPostAction($postAction);
        }

        $this->listPostAction->execute($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPostActionMock()
    {
        if (!$this->postActionBuilder) {
            $this->postActionBuilder =
                $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
                    ->setMethods(array('execute'))
                    ->disableOriginalConstructor();
        }

        return $this->postActionBuilder->getMockForAbstractClass();
    }
}
