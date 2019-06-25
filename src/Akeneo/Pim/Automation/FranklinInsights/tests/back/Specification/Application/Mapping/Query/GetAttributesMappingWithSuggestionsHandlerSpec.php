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
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
    ): void {
        $this->beConstructedWith($getAttributesMappingByFamilyHandler, $selectExactMatchAttributeCodeQuery);
    }

    public function it_is_a_get_attributes_mapping_with_suggestions_query_handler(): void
    {
        $this->shouldHaveType(GetAttributesMappingWithSuggestionsHandler::class);
    }

    public function it_handles_a_get_attributes_mapping_with_suggestions_query(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
    ) {
        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );

        $getAttributesMappingByFamilyHandler
            ->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($attributeMappingCollection);

        $selectExactMatchAttributeCodeQuery
            ->execute(new FamilyCode('router'), ['Color', 'Weight'])
            ->willReturn(['Color' => 'color', 'Weight' => null]);

        $expectedMapping = new AttributeMappingCollection();
        $expectedMapping->addAttribute(
            new AttributeMapping('color', 'Color', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $expectedMapping->addAttribute(
            new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $expectedMapping->addAttribute(
            new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );
        $this->handle(new GetAttributesMappingWithSuggestionsQuery(new FamilyCode('router')))->shouldBeLike($expectedMapping);
    }

    public function it_adds_suggestions_only_for_pending_attribute_mappings(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
    ) {
        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE)
        );
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );

        $getAttributesMappingByFamilyHandler
            ->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($attributeMappingCollection);

        $selectExactMatchAttributeCodeQuery
            ->execute(new FamilyCode('router'), ['Color'])
            ->willReturn(['Color' => 'color']);

        $expectedMapping = new AttributeMappingCollection();
        $expectedMapping->addAttribute(
            new AttributeMapping('color', 'Color', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $expectedMapping->addAttribute(
            new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE)
        );
        $expectedMapping->addAttribute(
            new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );
        $this->handle(new GetAttributesMappingWithSuggestionsQuery(new FamilyCode('router')))->shouldBeLike($expectedMapping);
    }

    public function it_does_not_add_suggestion_if_the_suggested_attribute_is_already_mapped(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
    ) {
        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $attributeMappingCollection->addAttribute(
            new AttributeMapping('finish', 'Color/finish', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );

        $getAttributesMappingByFamilyHandler
            ->handle(new GetAttributesMappingByFamilyQuery(new FamilyCode('router')))
            ->willReturn($attributeMappingCollection);

        $selectExactMatchAttributeCodeQuery
            ->execute(new FamilyCode('router'), ['Color'])
            ->willReturn(['Color' => 'color']);

        $expectedMapping = new AttributeMappingCollection();
        $expectedMapping->addAttribute(
            new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING)
        );
        $expectedMapping->addAttribute(
            new AttributeMapping('finish', 'Color/finish', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_ACTIVE)
        );
        $this->handle(new GetAttributesMappingWithSuggestionsQuery(new FamilyCode('router')))->shouldBeLike($expectedMapping);
    }
}
