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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $this->beConstructedWith($attributeOptionsMappingProvider, $familyRepository);
    }

    public function it_is_a_get_attribute_option_mapping_handler(): void
    {
        $this->shouldBeAnInstanceOf(GetAttributeOptionsMappingHandler::class);
    }

    public function it_throws_an_exception_when_the_family_does_not_exist($familyRepository): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');
        $query = new GetAttributeOptionsMappingQuery($familyCode, $franklinAttributeId);

        $familyRepository->findOneByIdentifier($familyCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$query]);
    }

    public function it_returns_an_attribute_options_mapping(
        $familyRepository,
        $attributeOptionsMappingProvider,
        FamilyInterface $family
    ): void {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');
        $query = new GetAttributeOptionsMappingQuery($familyCode, $franklinAttributeId);

        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);

        $attributeOptionsMapping = new AttributeOptionsMapping('foo', 'bar', []);
        $attributeOptionsMappingProvider->getAttributeOptionsMapping('foo', 'bar')->willReturn($attributeOptionsMapping);

        $this->handle($query)->shouldReturn($attributeOptionsMapping);
    }
}
