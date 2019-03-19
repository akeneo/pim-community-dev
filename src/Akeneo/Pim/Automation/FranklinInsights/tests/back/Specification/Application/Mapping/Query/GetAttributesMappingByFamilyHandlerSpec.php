<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributesMappingProviderInterface $attributesMappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $this->beConstructedWith($attributesMappingProvider, $familyRepository, $attributeRepository);
    }

    public function it_is_a_get_attributes_mapping_query_handler(): void
    {
        $this->shouldHaveType(GetAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_the_family_does_not_exist(
        $familyRepository,
        $attributesMappingProvider
    ): void {
        $familyCode = new FamilyCode('unknown_family');
        $familyRepository->exist($familyCode)->willReturn(false);
        $attributesMappingProvider->getAttributesMapping($familyCode)->shouldNotBeCalled();

        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$query]);
    }

    public function it_handles_a_get_attributes_mapping_query(
        $familyRepository,
        $attributesMappingProvider,
        $attributeRepository
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute(new AttributeMapping('color', 'Color', 'text', 'pim_color', 1));
        $attributesMappingResponse->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', 1));

        $attributeRepository->findByCodes(['pim_color', 'pim_size'])->willReturn([
            AttributeBuilder::fromCode('pim_color'),
            AttributeBuilder::fromCode('pim_size'),
        ]);

        $familyCode = new FamilyCode('camcorders');
        $familyRepository->exist($familyCode)->willReturn(true);
        $attributesMappingProvider->getAttributesMapping($familyCode)->willReturn($attributesMappingResponse);

        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $this->handle($query)->shouldReturn($attributesMappingResponse);
    }

    public function it_handles_a_get_attributes_mapping_query_with_unknown_pim_attribute(
        $familyRepository,
        $attributesMappingProvider,
        $attributeRepository
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();
        $colorAttribute = new AttributeMapping('color', 'Color', 'text', 'pim_color', AttributeMappingStatus::ATTRIBUTE_ACTIVE);
        $unknownAttribute = new AttributeMapping('size', 'Size', 'text', 'unknown_pim_attribute', AttributeMappingStatus::ATTRIBUTE_ACTIVE);
        $attributesMappingResponse->addAttribute($colorAttribute);
        $attributesMappingResponse->addAttribute($unknownAttribute);

        $attributeRepository->findByCodes(['pim_color', 'unknown_pim_attribute'])->willReturn([
            AttributeBuilder::fromCode('pim_color'),
        ]);

        $familyCode = new FamilyCode('camcorders');
        $familyRepository->exist($familyCode)->willReturn(true);
        $attributesMappingProvider->getAttributesMapping($familyCode)->willReturn($attributesMappingResponse);

        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $expectedMappingResponse = new AttributesMappingResponse();
        $expectedMappingResponse->addAttribute($colorAttribute);
        $expectedMappingResponse->addAttribute(new AttributeMapping('size', 'Size', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING));
        $this->handle($query)->shouldBeLike($expectedMappingResponse);
    }

    public function it_filters_unknown_attributes(
        $familyRepository,
        $attributesMappingProvider,
        $attributeRepository
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute(new AttributeMapping(
            'series',
            null,
            'text',
            'unknown_pim_attr_code',
            AttributeMappingStatus::ATTRIBUTE_ACTIVE
        ));

        $familyCode = new FamilyCode('camcorders');
        $familyRepository->exist($familyCode)->willReturn(true);
        $attributesMappingProvider->getAttributesMapping($familyCode)->willReturn($attributesMappingResponse);

        $attributeRepository->findByCodes(['unknown_pim_attr_code'])->willReturn([]);

        $query = new GetAttributesMappingByFamilyQuery($familyCode);

        $expectedMapping = new AttributesMappingResponse();
        $expectedMapping->addAttribute(new AttributeMapping(
            'series',
            null,
            'text',
            null,
            AttributeMappingStatus::ATTRIBUTE_PENDING
        ));
        $this->handle($query)->shouldBeLike($expectedMapping);
    }
}
