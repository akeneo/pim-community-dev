<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\AttributesMappingController;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\FamiliesMappingStatusNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AttributesMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesMappingStatusNormalizer $familiesNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        SecurityFacade $securityFacade
    ): void {
        $this->beConstructedWith(
            $getAttributesMappingByFamilyHandler,
            $saveAttributesMappingByFamilyHandler,
            $searchFamiliesHandler,
            $familiesNormalizer,
            $attributesMappingNormalizer,
            $securityFacade
        );
    }

    public function it_is_an_attributes_mapping_normalizer(): void
    {
        $this->shouldHaveType(AttributesMappingController::class);
    }

    public function it_redirects_to_home_during_update_if_request_is_not_xml_http(Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(false);
        $response = $this->updateAction('camcorders', $request);

        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/');
    }

    public function it_throws_an_exception_during_list_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade
    ): void {
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);
        $this->shouldThrow(new AccessDeniedException())->during('listAction', [new Request()]);
    }

    public function it_throws_an_exception_during_get_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade
    ): void {
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);
        $this->shouldThrow(new AccessDeniedException())->during('getAction', ['familyeCode']);
    }

    public function it_throws_an_exception_during_update_if_the_user_is_not_granted_the_settings_permission(
        $securityFacade,
        Request $request
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')->willReturn(false);
        $this->shouldThrow(new AccessDeniedException())->during('updateAction', ['familyCode', $request]);
    }
}
