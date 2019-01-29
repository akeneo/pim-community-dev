<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\IdentifiersMappingController;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class IdentifiersMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
    ): void {
        $this->beConstructedWith($getIdentifiersMappingHandler, $saveIdentifiersMappingHandler);
    }

    public function it_is_an_identifiers_mapping_controller(): void
    {
        $this->shouldHaveType(IdentifiersMappingController::class);
    }

    public function it_redirects_to_home_during_update_if_request_is_not_xml_http(Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(false);
        $response = $this->saveIdentifiersMappingAction($request);

        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/');
    }
}
