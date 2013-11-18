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

        $configurationPass = $this->getMockBuilder(
            'Oro\Bundle\WorkflowBundle\Model\ConfigurationPass\ConfigurationPassInterface'
        )->getMockForAbstractClass();

        $configurationPass->expects($this->any())
            ->method('passConfiguration')
            ->will(
                $this->returnCallback(
                    function ($options) {
                        return array('passed' => $options);
                    }
                )
            );

        $assembler = new ConditionAssembler($factory);
        $assembler->addConfigurationPass($configurationPass);

        $actual = $assembler->assemble($configuration);
        $this->assertEquals($expected, $actual);
    }

    public function configurationDataProvider()
    {
        return array(
            array(
                array(
                    '@or' => array(
                        'parameters' => array(
                            array(
                                '@and' => array(
                                    array('@graterOrEquals' =>
                                        array('parameters' => array('$contact.budget', 2000))
                                    ),
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
                        ),
                        'message' => 'Or fail'
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
