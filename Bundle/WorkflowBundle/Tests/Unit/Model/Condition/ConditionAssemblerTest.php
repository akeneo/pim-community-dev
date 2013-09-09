<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\ParameterPass;

class ConditionAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configurationDataProvider
     * @param $configuration
     * @param $expected
     */
    public function testAssemble($configuration, $expected)
    {
        $factory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($type, $options) {
                        return array('_' . $type => $options);
                    }
                )
            );

        $parametersPass = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface')
            ->getMockForAbstractClass();
        $parametersPass->expects($this->any())
            ->method('pass')
            ->will(
                $this->returnCallback(
                    function ($options) {
                        return array('passed' => $options);
                    }
                )
            );
        $assembler = new ConditionAssembler($factory, $parametersPass);
        $actual = $assembler->assemble($configuration);
        $this->assertEquals($expected, $actual);
    }

    public function configurationDataProvider()
    {
        return array(
            array(
                array(
                    '@or' => array(
                        array(
                            '@and' => array(
                                array('@graterOrEquals' => array('$contact.budget', 2000)),
                                array('@isDevMode' => null),
                                array(
                                    '@inChoiceList' => array(
                                        'type' => '$contact.type',
                                        array('a' => 1, 'b' => 2)
                                    )
                                )
                            )
                        ),
                        array(
                            '@notEmpty' => array('$lead.name')
                        )
                    )
                ),
                array(
                    '_or' => array(
                        'passed' => array(
                            array(
                                '_and' => array(
                                    'passed' => array(
                                        array('_graterOrEquals' => array('passed' => array('$contact.budget', 2000))),
                                        array('_isDevMode' => array('passed' => array(null))),
                                        array(
                                            '_inChoiceList' => array(
                                                'passed' => array(
                                                    'type' => '$contact.type',
                                                    array('a' => 1, 'b' => 2)
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            array(
                                '_notEmpty' => array('passed' => array('$lead.name'))
                            )
                        )
                    )
                )
            ),
            array(
                array(), null
            )
        );
    }
}
