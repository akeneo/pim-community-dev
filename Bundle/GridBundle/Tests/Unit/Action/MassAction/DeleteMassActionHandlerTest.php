<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\DeleteMassActionHandler;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponse;

class DeleteMassActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteMassActionHandler */
    protected $handler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mediator;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->handler = new DeleteMassActionHandler($this->em, $this->translator);

        $this->mediator = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface');
    }


    public function testHandle()
    {
        $entitiesCount = 21;

        $entities = array();
        $results = array();

        $emExpectedCalls = array();
        $emExpectedCalls[] = array('beginTransaction', array());

        for ($i = 0; $i < $entitiesCount; $i++) {
            $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
            $entities[] = $entity;

            $result = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
            $results[] = $result;

            $this->addMockExpectedCalls(
                array(
                    'mock' => $result,
                    'expectedCalls' => array(
                        array('getRootEntity', array(), $this->returnValue($entity))
                    )
                )
            );

            if ($i == DeleteMassActionHandler::FLUSH_BATCH_SIZE) {
                $emExpectedCalls[] = array('flush', array());
            }

            $emExpectedCalls[] = array('remove', array($entity));
        }
        $emExpectedCalls[] = array('flush', array());
        $emExpectedCalls[] = array('commit', array());

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->mediator,
                'expectedCalls' => array(
                    array('getResults', array(), $this->returnValue($results)),
                    array('getMassAction', array(), $this->returnValue($massAction)),
                )
            ),
            array(
                'mock' => $this->em,
                'expectedCalls' => $emExpectedCalls
            ),
            array(
                'mock' => $massAction,
                'expectedCalls' => array(
                    array('getOption', array('messages'), $this->returnValue(array('success' => 'success_message')))
                ),
            ),
            array(
                'mock' => $this->translator,
                'expectedCalls' => array(
                    array(
                        'transChoice',
                        array('success_message', $entitiesCount, array('%count%' => $entitiesCount)),
                        $this->returnValue('translated_success_message')
                    )
                ),
            )
        );

        $this->assertEquals(
            new MassActionResponse(
                true,
                'translated_success_message',
                array('count' => $entitiesCount)
            ),
            $this->handler->handle($this->mediator)
        );
    }

    protected function addMockExpectedCalls()
    {
        $mocksExpectedCalls = func_get_args();
        foreach ($mocksExpectedCalls as $mockExpectedCalls) {
            /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
            list($mock, $expectedCalls) = array_values($mockExpectedCalls);
            if ($expectedCalls) {
                $index = 0;
                foreach ($expectedCalls as $expectedCall) {
                    $expectedCall = array_pad($expectedCall, 3, null);
                    list($method, $arguments, $result) = $expectedCall;
                    $methodExpectation = $mock->expects(\PHPUnit_Framework_TestCase::at($index++))->method($method);
                    $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                    if ($result) {
                        $methodExpectation->will($result);
                    }
                }
            } else {
                $mock->expects(\PHPUnit_Framework_TestCase::never())->method(\PHPUnit_Framework_TestCase::anything());
            }
        };
    }
}
