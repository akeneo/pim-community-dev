<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\IdentifiersMappingController;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IdentifiersMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler,
        SecurityFacade $securityFacade
    ): void {
        $this->beConstructedWith($getIdentifiersMappingHandler, $saveIdentifiersMappingHandler, $securityFacade);
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

    public function it_throw_an_exception_during_get_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade
    ): void {
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);
        $this->shouldThrow(new AccessDeniedException())->during('getIdentifiersMappingAction', []);
    }

    public function it_throw_an_exception_during_save_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade,
        Request $request
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);
        $this->shouldThrow(new AccessDeniedException())->during('saveIdentifiersMappingAction', [$request]);
    }
}
