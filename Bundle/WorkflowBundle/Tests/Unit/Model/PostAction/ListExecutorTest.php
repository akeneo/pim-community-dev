<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\ListExecutor;

class ListExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListExecutor
     */
    protected $listPostAction;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $postActionBuilder;

    protected function setUp()
    {
        $this->listPostAction = new ListExecutor();
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
            $breakOnFailure = (bool)$i % 2;
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
     * @expectedException \Exception
     * @expectedExceptionMessage TEST
     */
    public function testBreakOnFailureEnabledException()
    {
        $postActionError = $this->getExceptionPostAction();
        $postAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->getMockForAbstractClass();
        $postAction->expects($this->never())
            ->method('execute');
        $this->listPostAction->addPostAction($postActionError, true);
        $this->listPostAction->addPostAction($postAction);
        $this->listPostAction->execute(array());
    }

    public function testBreakOnFailureDisabledException()
    {
        $postActionError = $this->getExceptionPostAction();
        $postAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->getMockForAbstractClass();
        $postAction->expects($this->once())
            ->method('execute');
        $this->listPostAction->addPostAction($postActionError, false);
        $this->listPostAction->addPostAction($postAction);
        $this->listPostAction->execute(array());
    }

    public function testBreakOnFailureDisabledLogException()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();
        $logger->expects($this->once())
            ->method('log')
            ->with('ALERT', 'TEST');
        $listPostAction = new ListExecutor($logger);
        $postActionError = $this->getExceptionPostAction();
        $listPostAction->addPostAction($postActionError, false);
        $listPostAction->execute(array());
    }

    protected function getExceptionPostAction()
    {
        $postAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->setMethods(array('execute'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $postAction->expects($this->once())
            ->method('execute')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception('TEST');
                    }
                )
            );
        return $postAction;
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
