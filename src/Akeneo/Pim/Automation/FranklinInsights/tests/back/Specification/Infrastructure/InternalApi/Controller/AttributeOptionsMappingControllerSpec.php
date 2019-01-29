<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\AttributeOptionsMappingController;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AttributeOptionsMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
    ): void {
        $this->beConstructedWith($getAttributeOptionsMappingHandler, $saveAttributeOptionsMappingHandler);
    }

    public function it_is_an_attribute_options_mapping_controller(): void
    {
        $this->shouldHaveType(AttributeOptionsMappingController::class);
    }

    public function it_redirects_to_home_during_update_if_request_is_not_xml_http(Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(false);
        $response = $this->updateAction($request);

        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/');
    }
}
