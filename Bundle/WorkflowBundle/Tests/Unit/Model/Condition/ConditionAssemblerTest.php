<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler;

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

        $assembler = new ConditionAssembler($factory);
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
                    '_@or' => array(
                        array(
                            '_@and' => array(
                                array('_@graterOrEquals' => array('$contact.budget', 2000)),
                                array('_@isDevMode' => array(null)),
                                array(
                                    '_@inChoiceList' => array(
                                        'type' => '$contact.type',
                                        array('a' => 1, 'b' => 2)
                                    )
                                )
                            )
                        ),
                        array(
                            '_@notEmpty' => array('$lead.name')
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
