<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductDraftPresentersPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf(CompilerPassInterface::class);
    }

    function it_adds_tagged_presenter_to_the_product_draft_twig_extension(
        ContainerBuilder $container,
        Definition $twigExt,
        Definition $barDefinition,
        Definition $fooDefinition,
        Definition $bazDefinition
    ) {
        $container->hasDefinition('pimee_workflow.presenter.registry')->willReturn(true);
        $container->getDefinition('pimee_workflow.presenter.registry')->willReturn($twigExt);
        $container->findTaggedServiceIds('pimee_workflow.presenter')->willReturn([
            'presenter.bar' => [['priority' => -10]],
            'presenter.foo' => [['priority' => 10]],
            'presenter.baz' => [[]],
        ]);
        $container->getDefinition('presenter.bar')->willReturn($barDefinition);
        $container->getDefinition('presenter.foo')->willReturn($fooDefinition);
        $container->getDefinition('presenter.baz')->willReturn($bazDefinition);

        $barDefinition->setPublic(false)->shouldBeCalled();
        $fooDefinition->setPublic(false)->shouldBeCalled();
        $bazDefinition->setPublic(false)->shouldBeCalled();
        $twigExt
            ->addMethodCall('addPresenter', $this->isAnArrayContainingAReferenceAndAPriority('presenter.bar', -10))
            ->shouldBeCalled();
        $twigExt
            ->addMethodCall('addPresenter', $this->isAnArrayContainingAReferenceAndAPriority('presenter.foo', 10))
            ->shouldBeCalled();
        $twigExt
            ->addMethodCall('addPresenter', $this->isAnArrayContainingAReferenceAndAPriority('presenter.baz', 0))
            ->shouldBeCalled();

        $this->process($container);
    }

    private function isAnArrayContainingAReferenceAndAPriority($service, $priority)
    {
        return Argument::allOf(
            Argument::withEntry(0, Argument::allOf(
                Argument::type('Symfony\Component\DependencyInjection\Reference'),
                Argument::which('__toString', $service)
            ))
        );
    }
}
