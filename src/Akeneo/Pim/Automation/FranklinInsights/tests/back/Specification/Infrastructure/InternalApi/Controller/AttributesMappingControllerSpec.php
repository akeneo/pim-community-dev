<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\AttributesMappingController;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\FamiliesNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AttributesMappingControllerSpec extends ObjectBehavior
{
    public function let(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesNormalizer $familiesNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer
    ): void {
        $this->beConstructedWith(
            $getAttributesMappingByFamilyHandler,
            $saveAttributesMappingByFamilyHandler,
            $searchFamiliesHandler,
            $familiesNormalizer,
            $attributesMappingNormalizer
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
}
