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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributesMappingProviderInterface $attributesMappingProvider,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $this->beConstructedWith($attributesMappingProvider, $familyRepository);
    }

    public function it_is_a_get_attributes_mapping_query_handler(): void
    {
        $this->shouldHaveType(GetAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_the_family_does_not_exist(
        $familyRepository,
        $attributesMappingProvider
    ): void {
        $familyRepository->findOneByIdentifier('unknown_family')->willReturn(null);
        $attributesMappingProvider->getAttributesMapping('unknown_family')->shouldNotBeCalled();

        $query = new GetAttributesMappingByFamilyQuery('unknown_family');
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$query]);
    }

    public function it_handles_a_get_attributes_mapping_query(
        FamilyInterface $family,
        $familyRepository,
        $attributesMappingProvider
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();

        $familyRepository->findOneByIdentifier('camcorders')->willReturn($family);
        $attributesMappingProvider->getAttributesMapping('camcorders')->willReturn($attributesMappingResponse);

        $query = new GetAttributesMappingByFamilyQuery('camcorders');
        $this->handle($query)->shouldReturn($attributesMappingResponse);
    }
}
