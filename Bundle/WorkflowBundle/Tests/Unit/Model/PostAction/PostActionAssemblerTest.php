<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionAssembler;
use Oro\Bundle\WorkflowBundle\Model\PostAction\ListExecutor;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub\ArrayPostAction;

class PostActionAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $source
     * @param array $expected
     *
     * @dataProvider assembleDataProvider
     */
    public function testAssemble(array $source, array $expected)
    {
        // all actual post actions will be collected in $actualPostActions
        $actualPostActions = array();
        $listPostAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\ListPostAction')
            ->setMethods(array('addPostAction'))
            ->getMock();
        $listPostAction->expects($this->exactly(count($source)))
            ->method('addPostAction')
            ->will(
                $this->returnCallback(
                    function ($postAction) use (&$actualPostActions) {
                        $actualPostActions[] = $postAction;
                    }
                )
            );
        for ($i = 0; $i < count($source); $i++) {
            $postActionConfig = array_values($source[$i]);
            $listPostAction->expects($this->at($i))
                ->method('addPostAction')
                ->with($this->anything(), !empty($postActionConfig[0]['breakOnFailure']));
        }

        $factory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $factory->expects($this->at(0))
            ->method('create')
            ->with(ListExecutor::ALIAS)
            ->will($this->returnValue($listPostAction));
        $factory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($type, $options) {
                        $postAction = new ArrayPostAction(array('_type' => $type));
                        $postAction->initialize($options);
                        return $postAction;
                    }
                )
            );

        $pass = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('pass'))
            ->getMockForAbstractClass();
        $pass->expects($this->any())
            ->method('pass')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function (array $data) {
                        $data['_pass'] = true;
                        return $data;
                    }
                )
            );

        $assembler = new PostActionAssembler($factory, $pass);
        $this->assertEquals($listPostAction, $assembler->assemble($source));

        // convert ArrayPostAction to array
        /** @var ArrayPostAction $postAction */
        foreach ($actualPostActions as $key => $postAction) {
            $actualPostActions[$key] = $postAction->toArray();
        }

        $this->assertEquals($expected, $actualPostActions);
    }

    /**
     * @return array
     */
    public function assembleDataProvider()
    {
        return array(
            'empty configuration' => array(
                'source'   => array(),
                'expected' => array(),
            ),
            'not empty configuration' => array(
                'source' => array(
                    array(
                        '@create_new_entity' => array(
                            'parameters' => array('class_name' => 'TestClass'),
                        )
                    ),
                    array(
                        '@assign_value' => array(
                            'parameters' => array('from' => 'name', 'to' => 'contact.name'),
                            'breakOnFailure' => true,
                        )
                    ),
                ),
                'expected' => array(
                    array(
                        '_type' => '@create_new_entity',
                        'parameters' => array('class_name' => 'TestClass', '_pass' => true)
                    ),
                    array(
                        '_type' => '@assign_value',
                        'parameters' => array('from' => 'name', 'to' => 'contact.name', '_pass' => true)
                    ),
                ),
            )
        );
    }
}
