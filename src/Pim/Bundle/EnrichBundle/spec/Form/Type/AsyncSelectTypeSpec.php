<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Gedmo\Tree\RepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Factory\IdentifiableModelTransformerFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AsyncSelectTypeSpec extends ObjectBehavior
{
    function let(RouterInterface $router, IdentifiableModelTransformerFactory $transformerFactory)
    {
        $this->beConstructedWith($router, $transformerFactory);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_async_select');
    }

    function it_has_a_hidden_parent()
    {
        $this->getParent()->shouldReturn('hidden');
    }

    function it_builds_form(
        FormBuilderInterface $builder,
        IdentifiableObjectRepositoryInterface $repository,
        DataTransformerInterface $transformer,
        $transformerFactory
    ) {
        $options = [
            'repository' => $repository->getWrappedObject(),
            'multiple'   => false,
        ];

        $transformerFactory->create($repository, ['multiple' => false])->willReturn($transformer);

        $builder->addViewTransformer($transformer, true)->shouldBeCalled();

        $this->buildForm($builder, $options);
    }

    function it_acept_only_identifiable_object_repository_interface(
        FormBuilderInterface $builder,
        RepositoryInterface $repository
    ) {
        $options = [
            'repository' => $repository->getWrappedObject()
        ];

        $this->shouldThrow('\Symfony\Component\Form\Exception\UnexpectedTypeException')
            ->duringBuildForm($builder, $options);
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'repository_options' => [],
                'route_parameters'   => [],
                'required'           => false,
                'multiple'           => false,
                'min-input-length'   => 0,
            ]
        )->shouldBeCalled()->willReturn($resolver);

        $resolver->setAllowedTypes('repository_options', ['array'])->willReturn($resolver);
        $resolver->setAllowedTypes('route_parameters', ['array'])->willReturn($resolver);
        $resolver->setAllowedTypes('required', ['bool'])->willReturn($resolver);
        $resolver->setAllowedTypes('multiple', ['bool'])->willReturn($resolver);
        $resolver->setAllowedTypes('min-input-length', ['int'])->willReturn($resolver);

        $resolver->setRequired(['route', 'repository'])->shouldBeCalled();

        $this->configureOptions($resolver);
    }
}
