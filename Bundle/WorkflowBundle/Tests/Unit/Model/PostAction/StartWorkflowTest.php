<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\PostAction\StartWorkflow;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class StartWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StartWorkflow
     */
    protected $postAction;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     */
    protected $workflowManager;

    protected function setUp()
    {
        $this->workflowManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowManager')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $this->postAction = new StartWorkflow(new ContextAccessor(), $this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->workflowManager);
        unset($this->postAction);
    }

    /**
     * @param array $options
     * @dataProvider initializeDataProvider
     */
    public function testInitialize(array $options)
    {
        $this->postAction->initialize($options);
        $this->assertAttributeEquals($options, 'options', $this->postAction);
    }

    public function initializeDataProvider()
    {
        $context = array(
            'workflowName' => 'acmeWorkflow',
            'entityValue' => new \DateTime('now'),
            'startTransition' => 'acmeStartTransition',
        );

        return array(
            'minimum options' => array(
                'options' => array(
                    'name' => 'acmeWorkflow',
                    'attribute' => new PropertyPath('workflowItem'),
                )
            ),
            'maximum options' => array(
                'options' => array(
                    'name' => new PropertyPath('workflowName'),
                    'attribute' => new PropertyPath('workflowItem'),
                    'entity' => new PropertyPath('entityValue'),
                    'transition' => new PropertyPath('startTransition'),
                )
            ),
        );
    }
}
