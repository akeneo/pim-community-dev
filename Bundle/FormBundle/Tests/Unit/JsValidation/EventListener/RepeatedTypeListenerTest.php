<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\JsValidation\Event;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FormBundle\JsValidation\Event\PostProcessEvent;
use Oro\Bundle\FormBundle\JsValidation\EventListener\RepeatedTypeListener;

class RepeatedTypeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RepeatedTypeListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new RepeatedTypeListener();
    }

    /**
     * @dataProvider onPostProcessDataProvider
     */
    public function testOnPostProcess(FormView $formView, array $expectedVars, array $expectedChildrenVars)
    {
        $event = new PostProcessEvent($formView);
        $this->listener->onPostProcess($event);

        $this->assertEquals($expectedVars, $formView->vars);
        $this->assertEquals(count($expectedChildrenVars), count($formView->children));

        foreach ($expectedChildrenVars as $childName => $expectedVars) {
            $this->assertArrayHasKey($childName, $formView->children);
            $this->assertEquals($expectedVars, $formView->children[$childName]->vars);
        }
    }

    public function onPostProcessDataProvider()
    {
        return array(
            'not_repeated' => array(
                'formView' => $this->createFormView(),
                'expectedVars' => array(),
                'expectedChildrenVars' => array()
            ),
            'repeated' => array(
                'formView' => $this->createFormView(
                    array(
                        'type' => 'repeated',
                        'value' => array(
                            'first' => null,
                            'second' => null,
                        ),
                        'attr' => array(
                            'data-validation' => array('NotBlank' => array())
                        ),
                        'invalid_message' => 'Some invalid message',
                        'invalid_message_parameters' => array(1),
                    ),
                    array(
                        'first' => $this->createFormView(),
                        'second' => $this->createFormView(),
                    )
                ),
                'expectedVars' => array(
                    'type' => 'repeated',
                    'value' => array(
                        'first' => null,
                        'second' => null,
                    ),
                    'attr' => array(),
                    'invalid_message' => 'Some invalid message',
                    'invalid_message_parameters' => array(1),
                ),
                'expectedChildrenVars' => array(
                    'first' => array(
                        'attr' => array(
                            'data-validation' => array('NotBlank' => array())
                        )
                    ),
                    'second' => array(
                        'attr' => array(
                            'data-validation' => array(
                                'Repeated' => array(
                                    'first_name' => 'first',
                                    'second_name' => 'second',
                                    'invalid_message' => 'Some invalid message',
                                    'invalid_message_parameters' => array(1),
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @param array $vars
     * @param array $children
     * @return FormView
     */
    protected function createFormView(array $vars = array(), array $children = array())
    {
        $result = new FormView();
        $result->vars = $vars;
        $result->children = $children;
        return $result;
    }
}
