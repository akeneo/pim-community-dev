<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\RuleController;
use Akeneo\Pim\Enrichment\Product\Bundle\Controller\InternalApi\DuplicateProductController;
use Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext\BoundedContextResolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class BoundedContextResolverSpec extends ObjectBehavior
{
    function it_resolves_context_from_request(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        DuplicateProductController $fooController
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $controllerResolver->getController($request)->willReturn(false);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldReturn('Unknown request context');
    }

    function it_cannot_resolves_context_from_request(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        RuleController $fooController
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $controllerResolver->getController($request)->willReturn($fooController);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldContain(
            'Unknown namespace context: Double\\\Akeneo\\\Pim\\\Automation\\\RuleEngine\\\Bundle\\\Controller\\\RuleController'
        );
    }

    function it_cannot_resolves_context_from_namespace(
        ControllerResolverInterface $controllerResolver,
        Request $request,
        RuleController $fooController
    ) {
        $boundedContexts = [
            'Double\Akeneo\Pim\Enrichment' => 'Enrichment',
        ];

        $controllerResolver->getController($request)->willReturn($fooController);

        $this->beConstructedWith($controllerResolver, $boundedContexts);
        $this->shouldHaveType(BoundedContextResolver::class);

        $this->fromRequest($request)->shouldContain(
            'Unknown namespace context: Double\\\Akeneo\\\Pim\\\Automation\\\RuleEngine\\\Bundle\\\Controller\\\RuleController'
        );
    }
}
