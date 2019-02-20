<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\AttributeOptionsMappingController;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AttributeOptionsMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler,
        SecurityFacade $securityFacade
    ): void {
        $this->beConstructedWith(
            $getAttributeOptionsMappingHandler,
            $saveAttributeOptionsMappingHandler,
            $securityFacade
        );
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

    public function it_throws_an_exception_during_get_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade
    ): void {
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('getAction', ['familyCode', 'franklin_code']);
    }

    public function it_throws_an_exception_during_update_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade,
        Request $request
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('updateAction', [$request]);
    }
}
