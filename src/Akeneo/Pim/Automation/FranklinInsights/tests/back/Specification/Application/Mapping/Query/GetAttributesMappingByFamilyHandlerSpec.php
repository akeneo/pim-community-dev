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
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
        $familyRepository->exist(Argument::that(function ($familyCode) {
            return $familyCode instanceof FamilyCode && 'unknown_family' === (string) $familyCode;
        }))->willReturn(false);
        $attributesMappingProvider->getAttributesMapping('unknown_family')->shouldNotBeCalled();

        $query = new GetAttributesMappingByFamilyQuery('unknown_family');
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$query]);
    }

    public function it_handles_a_get_attributes_mapping_query(
        $familyRepository,
        $attributesMappingProvider
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();

        $familyRepository->exist(Argument::that(function ($familyCode) {
            return $familyCode instanceof FamilyCode && 'camcorders' === (string) $familyCode;
        }))->willReturn(true);
        $attributesMappingProvider->getAttributesMapping('camcorders')->willReturn($attributesMappingResponse);

        $query = new GetAttributesMappingByFamilyQuery('camcorders');
        $this->handle($query)->shouldReturn($attributesMappingResponse);
    }

    public function it_filters_unknown_attributes(
        $familyRepository,
        $attributesMappingProvider,
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute(new AttributeMapping(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMappingStatus::ATTRIBUTE_ACTIVE
        ));

        $familyRepository->exist(Argument::that(function ($familyCode) {
            return $familyCode instanceof FamilyCode && 'camcorders' === (string) $familyCode;
        }))->willReturn(true);
        $attributesMappingProvider->getAttributesMapping('camcorders')->willReturn($attributesMappingResponse);

        $attributeRepository->findBy(['code' => ['pim_series']])->willReturn($attribute);

        $query = new GetAttributesMappingByFamilyQuery('camcorders');

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
