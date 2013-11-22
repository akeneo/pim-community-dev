<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\JsValidation\Event;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FormBundle\JsValidation\Event\GetConstraintsEvent;
use Oro\Bundle\FormBundle\JsValidation\EventListener\RequiredConstraintListener;

use Symfony\Component\Validator\Constraints;

class RequiredConstraintListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequiredConstraintListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new RequiredConstraintListener();
    }

    /**
     * @dataProvider onGetConstraintsDataProvider
     */
    public function testOnGetConstraints(FormView $formView, array $constraints, array $expectedConstraints)
    {
        $constraints = new ArrayCollection($constraints);
        $event = new GetConstraintsEvent($formView, $constraints);
        $this->listener->onGetConstraints($event);

        $this->assertEquals($expectedConstraints, $constraints->getValues());
    }

    public function onGetConstraintsDataProvider()
    {
        return array(
            'required_but_compound' => array(
                'formView' => $this->createFormView(array('compound' => true, 'required' => true)),
                'constraints' => array(),
                'expectedConstraints' => array(),
            ),
            'not_required' => array(
                'formView' => $this->createFormView(array('required' => false)),
                'constraints' => array(),
                'expectedConstraints' => array(),
            ),
            'required_already_with_not_null_constraint' => array(
                'formView' => $this->createFormView(array('required' => true)),
                'constraints' => array(new Constraints\NotNull()),
                'expectedConstraints' => array(new Constraints\NotNull()),
            ),
            'required_already_with_not_blank_constraint' => array(
                'formView' => $this->createFormView(array('required' => true)),
                'constraints' => array(new Constraints\NotBlank(array('message' => 'Custom message'))),
                'expectedConstraints' => array(new Constraints\NotBlank(array('message' => 'Custom message'))),
            ),
            'required_with_constraint' => array(
                'formView' => $this->createFormView(array('required' => true)),
                'constraints' => array(new Constraints\Email()),
                'expectedConstraints' => array(new Constraints\Email(), new Constraints\NotBlank()),
            ),
            'required_without_constraints' => array(
                'formView' => $this->createFormView(array('required' => true)),
                'constraints' => array(),
                'expectedConstraints' => array(new Constraints\NotBlank()),
            ),
        );
    }

    /**
     * @param array $vars
     * @return FormView
     */
    protected function createFormView(array $vars = array())
    {
        $result = new FormView();
        $result->vars = $vars;
        return $result;
    }
}
