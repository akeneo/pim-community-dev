<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FormExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Twig\FormExtension');
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
    }

    function it_has_function()
    {
        $this->getFunctions()->shouldBeArray();
    }

    function it_sort_form_types(FormView $formType, FormView $number, FormView $date, FormView $switch, FormView $text)
    {
        $formType->children = [
            $number,
            $date,
            $switch
        ];

        $text->vars = ['name' => 'switch'];
        $number->vars = ['name' => 'number'];
        $date->vars = ['name' => 'date'];
        $switch->vars = ['name' => 'switch'];

        $this->filterFormChildren($formType, ['switch', 'date', 'number'])->shouldReturn([
            $switch,
            $date,
            $number,
        ]);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_form_extension');
    }
}
