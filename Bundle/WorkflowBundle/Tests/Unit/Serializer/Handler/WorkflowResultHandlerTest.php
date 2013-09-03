<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Handler;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Serializer\Handler\WorkflowResultHandler;
use Oro\Bundle\WorkflowBundle\Model\WorkflowResult;

class WorkflowResultHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataManager;

    /**
     * @var WorkflowResultHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->metadataManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\MetadataManager')
            ->disableOriginalConstructor()
            ->setMethods(array('isManageableEntity', 'getEntityIdentifier'))
            ->getMock();
        $this->handler = new WorkflowResultHandler($this->metadataManager);
    }

    /**
     * @dataProvider workflowResultToJsonDataProvider
     *
     * @param WorkflowResult $result
     * @param array $metadataManagerExpectedCalls
     * @param mixed $expectedResult
     */
    public function testWorkflowResultToJson(
        WorkflowResult $result,
        array $metadataManagerExpectedCalls,
        $expectedResult
    ) {
        $visitor = $this->getMockBuilder('JMS\Serializer\JsonSerializationVisitor')
            ->disableOriginalConstructor()->getMock();
        $visitor->expects($this->never())->method($this->anything());
        $context = $this->getMockBuilder('JMS\Serializer\Context')
            ->disableOriginalConstructor()->getMock();
        $context->expects($this->never())->method($this->anything());

        if (!$metadataManagerExpectedCalls) {
            $this->metadataManager->expects($this->never())->method($this->anything());
        } else {
            $index = 0;
            foreach ($metadataManagerExpectedCalls as $expectedCall) {
                list($method, $arguments, $stub) = array_values($expectedCall);
                $mock = $this->metadataManager->expects($this->at($index++))->method($method);
                $mock = call_user_func_array(array($mock, 'with'), $arguments);
                $mock->will($stub);
            }
        }

        $type = array();
        $this->assertEquals($expectedResult, $this->handler->workflowResultToJson($visitor, $result, $type, $context));
    }

    public function workflowResultToJsonDataProvider()
    {
        $object = $this->getMock('PlainObject');
        $entity = $this->getMock('Entity');
        return array(
            'plain' => array(
                new WorkflowResult(
                    array(
                        'foo' => 'bar'
                    )
                ),
                array(),
                $this->createObjectFromArray(
                    array(
                        'foo' => 'bar'
                    )
                )
            ),
            'collection' => array(
                new WorkflowResult(
                    array(
                        'foo' => new ArrayCollection(
                            array(
                                'bar' => 'baz'
                            )
                        )
                    )
                ),
                'metadataManagerExpectedCalls' => array(
                    array(
                        'method' => 'isManageableEntity',
                        'with' => array($this->isInstanceOf('Doctrine\Common\Collections\ArrayCollection')),
                        'will' => $this->returnValue(false)
                    )
                ),
                $this->createObjectFromArray(
                    array(
                        'foo' => array(
                            'bar' => 'baz'
                        )
                    )
                )
            ),
            'object' => array(
                new WorkflowResult(
                    array(
                        'foo' => $object,
                    )
                ),
                'metadataManagerExpectedCalls' => array(
                    array(
                        'method' => 'isManageableEntity',
                        'with' => array($object),
                        'will' => $this->returnValue(false)
                    )
                ),
                $this->createObjectFromArray(
                    array(
                        'foo' => $object
                    )
                )
            ),
            'entity' => array(
                new WorkflowResult(
                    array(
                        'foo' => $entity
                    )
                ),
                'metadataManagerExpectedCalls' => array(
                    array(
                        'method' => 'isManageableEntity',
                        'with' => array($entity),
                        'will' => $this->returnValue(true)
                    ),
                    array(
                        'method' => 'getEntityIdentifier',
                        'with' => array($entity),
                        'will' => $this->returnValue(array('id' => 100))
                    ),
                ),
                $this->createObjectFromArray(
                    array(
                        'foo' => array(
                            'id' => 100
                        )
                    )
                )
            ),
        );
    }

    protected function createObjectFromArray(array $data)
    {
        return (object)$data;
    }
}
