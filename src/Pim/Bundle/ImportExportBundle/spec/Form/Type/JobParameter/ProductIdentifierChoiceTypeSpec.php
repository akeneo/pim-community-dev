<?php

namespace spec\Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\ValidIdentifier;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class ProductIdentifierChoiceTypeSpec extends ObjectBehavior
{
    function let(RouterInterface $router)
    {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Form\Type\JobParameter\ProductIdentifierChoiceType');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('product_identifier', 'hidden', [
            'constraints' => new ValidIdentifier()
        ])->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_build_the_view($router, FormView $view, FormInterface $form, FormInterface $identifier)
    {
        $form->get('product_identifier')->willReturn($identifier);
        $identifier->getData()->willReturn('sku1, sku3');
        
        $router->generate('my_custom_tag')->willReturn('/my/custom/route');

        $this->buildView($view, $form, [
            'route' => 'my_custom_tag',
            'multiple' => true,
        ])->shouldReturn(null);

        assert($view->vars['choices'] === json_encode([
            ['id' => 'sku1', 'text' => 'sku1'],
            ['id' => 'sku3', 'text' => 'sku3'],
        ]), 'Invalid choices option');
        assert($view->vars['url'] === '/my/custom/route', 'Invlid URL option');
        assert($view->vars['multiple'] === true, 'Invalid multiple option');
    }

    function it_has_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data'      => true,
            'route_parameters'  => [],
            'multiple'          => false,
            'placeholder'       => null,
        ])->shouldBeCalled();

        $resolver->setDefined([
            'route_parameters',
            'placeholder',
            'multiple',
        ])->shouldBeCalled();

        $resolver->setRequired(['route'])->shouldBeCalled();

        $this->configureOptions($resolver)->shouldReturn(null);
    }

    function it_has_name()
    {
        $this->getName()->shouldReturn('pim_product_identifier_choice');
    }
}
