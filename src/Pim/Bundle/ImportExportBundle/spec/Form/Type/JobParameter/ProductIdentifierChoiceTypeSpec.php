<?php

namespace spec\Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class ProductIdentifierChoiceTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Form\Type\JobParameter\ProductIdentifierChoiceType');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('product_identifier', 'hidden')->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_build_the_view($router, FormView $view, FormInterface $form, FormInterface $identifier)
    {
        $form->get('product_identifier')->willReturn($identifier);
        $identifier->getData()->willReturn('sku1, sku3');

        $this->buildView($view, $form, [
            'multiple' => true,
        ])->shouldReturn(null);

        assert($view->vars['choices'] === json_encode([
            ['sku1'],
            ['sku3'],
        ]), 'Invalid choices option');
        assert($view->vars['multiple'] === true, 'Invalid multiple option');
    }

    function it_has_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data'      => true,
            'multiple'          => false,
            'placeholder'       => null,
        ])->shouldBeCalled();

        $resolver->setDefined([
            'placeholder',
            'multiple',
        ])->shouldBeCalled();

        $this->configureOptions($resolver)->shouldReturn(null);
    }

    function it_has_name()
    {
        $this->getName()->shouldReturn('pim_product_identifier_choice');
    }
}
