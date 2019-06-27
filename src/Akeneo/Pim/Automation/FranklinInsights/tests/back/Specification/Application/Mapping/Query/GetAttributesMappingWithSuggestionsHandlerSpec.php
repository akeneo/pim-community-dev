<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor\ApplyAttributeExactMatches;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor\SuggestAttributeExactMatchesFromOtherFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        ApplyAttributeExactMatches $applyAttributeExactMatchesDataProcessor,
        SuggestAttributeExactMatchesFromOtherFamily $suggestAttributeExactMatchesFromOtherFamilyDataProcessor
    ): void {
        $this->beConstructedWith($getAttributesMappingByFamilyHandler, $applyAttributeExactMatchesDataProcessor, $suggestAttributeExactMatchesFromOtherFamilyDataProcessor);
    }

    public function it_is_a_get_attributes_mapping_with_suggestions_query_handler(): void
    {
        $this->shouldHaveType(GetAttributesMappingWithSuggestionsHandler::class);
    }

    public function it_handles_a_get_attributes_mapping(
        $getAttributesMappingByFamilyHandler,
        $applyAttributeExactMatchesDataProcessor,
        $suggestAttributeExactMatchesFromOtherFamilyDataProcessor,
        AttributeMappingCollection $attributeMappingCollection,
        AttributeMappingCollection $processedAttributeMappingCollection
    ): void
    {
        $familyCode = new FamilyCode('family_code');
        $query = new GetAttributesMappingWithSuggestionsQuery($familyCode);

        $getAttributesMappingByFamilyHandler->handle(Argument::any())
            ->willReturn($attributeMappingCollection);

        $applyAttributeExactMatchesDataProcessor
            ->process($attributeMappingCollection, $familyCode)
            ->willReturn($processedAttributeMappingCollection);

        $suggestAttributeExactMatchesFromOtherFamilyDataProcessor
            ->process($processedAttributeMappingCollection, $familyCode)
            ->willReturn($processedAttributeMappingCollection);

        $this->handle($query)->shouldReturn($processedAttributeMappingCollection);
    }
}
