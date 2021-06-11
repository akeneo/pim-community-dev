<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\RuleController;
use Akeneo\Pim\Enrichment\Product\Bundle\Controller\InternalApi\DuplicateProductController;
use Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext\BoundedContextResolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class BoundedContextResolverSpec extends ObjectBehavior
{
    function it_resolves_context_from_request(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        ParameterBag $parameterBag,
        DuplicateProductController $fooController
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'FoundContext',
        ];

        $request->attributes = $parameterBag;
        $parameterBag->has('_controller')->willReturn(true);
        $controllerResolver->getController($request)->shouldBeCalled()->willReturn($fooController);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldReturn('FoundContext');
    }

    function it_cannot_resolves_context_from_namespace(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        ParameterBag $parameterBag,
        RuleController $fooController
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $request->attributes = $parameterBag;
        $parameterBag->has('_controller')->willReturn(true);
        $controllerResolver->getController($request)->willReturn($fooController);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldContain(
            'Unknown namespace context: Double\\\Akeneo\\\Pim\\\Automation\\\RuleEngine\\\Bundle\\\Controller\\\RuleController'
        );
    }

    function it_cannot_resolves_context_from_request_without_controller_attribute(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        ParameterBag $parameterBag
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $request->attributes = $parameterBag;
        $parameterBag->has('_controller')->willReturn(false);
        $controllerResolver->getController($request)->shouldNotBeCalled();

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldReturn('Unknown request context: no controller in request');
    }

    function it_cannot_resolves_context_from_request(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        ParameterBag $parameterBag
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $request->attributes = $parameterBag;
        $parameterBag->has('_controller')->willReturn(true);
        $controllerResolver->getController($request)->shouldBeCalled()->willReturn(false);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldReturn('Unknown request context: no controller in request');
    }
}
