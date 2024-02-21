<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\EditFormVisibilityChecker;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormInterface;

class EditFormVisibilityCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditFormVisibilityChecker::class);
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement(VisibilityCheckerInterface::class);
    }

    function it_checks_if_a_form_for_a_persisted_entity_exists_in_the_context(FormInterface $form)
    {
        $this->isVisible([], ['form' => $form])->shouldReturn(false);

        $value = new \stdClass();
        $value->id = 1;

        $form = new FormView();
        $form->vars['value'] = $value;

        $this->isVisible([], ['form' => $form])->shouldReturn(true);
    }

    function it_allows_configuring_the_property_path_to_check()
    {
        $this->isVisible(['path' => '[foo]'], ['foo' => 1])->shouldReturn(true);
    }
}
