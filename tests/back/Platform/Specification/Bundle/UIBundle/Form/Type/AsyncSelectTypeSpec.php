<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Gedmo\Tree\RepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Form\Factory\IdentifiableModelTransformerFactory;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\AsyncSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_async_select');
    }

    function it_has_a_hidden_parent()
    {
        $this->getParent()->shouldReturn(HiddenType::class);
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

        $this->shouldThrow(UnexpectedTypeException::class)
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
