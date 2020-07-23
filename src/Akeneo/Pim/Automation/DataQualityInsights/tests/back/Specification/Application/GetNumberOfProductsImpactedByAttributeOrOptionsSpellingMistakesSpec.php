<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use PhpSpec\ObjectBehavior;

final class GetNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakesSpec extends ObjectBehavior
{
    public function let(
        GetAttributeQueryInterface $getAttributeQuery,
        GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery,
        GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery
    ) {
        $this->beConstructedWith($getAttributeQuery, $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery, $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery);
    }

    public function it_returns_0_products_impacted_by_attribute_spelling_mistakes_for_a_text_attribute(
        $getAttributeQuery,
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery,
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery
    ) {
        $attributeCode = new AttributeCode('test');
        $attribute = new Attribute($attributeCode, AttributeType::text(), false, false);
        $getAttributeQuery->byAttributeCode($attributeCode)->willReturn($attribute);
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery->byAttributeCode($attributeCode)->willReturn(0);
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery->byAttribute($attribute)->shouldNotBeCalled();

        $this->byAttributeCode($attributeCode)->shouldReturn(0);
    }

    public function it_returns_the_number_of_products_impacted_by_attribute_spelling_mistakes_for_a_simple_select_attribute(
        $getAttributeQuery,
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery,
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery
    ) {
        $attributeCode = new AttributeCode('test');
        $attribute = new Attribute($attributeCode, AttributeType::simpleSelect(), false, false);
        $getAttributeQuery->byAttributeCode($attributeCode)->willReturn($attribute);
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery->byAttributeCode($attributeCode)->willReturn(3);
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery->byAttribute($attribute)->shouldNotBeCalled();

        $this->byAttributeCode($attributeCode)->shouldReturn(3);
    }

    public function it_returns_the_number_of_products_impacted_by_options_spelling_mistakes(
        $getAttributeQuery,
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery,
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery
    ) {
        $attributeCode = new AttributeCode('test');
        $attribute = new Attribute($attributeCode, AttributeType::simpleSelect(), false, false);
        $getAttributeQuery->byAttributeCode($attributeCode)->willReturn($attribute);
        $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery->byAttributeCode($attributeCode)->willReturn(0);
        $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery->byAttribute($attribute)->willReturn(39);

        $this->byAttributeCode($attributeCode)->shouldReturn(39);
    }

    public function it_returns_zero_when_attribute_does_not_exist(
        $getAttributeQuery
    ) {
        $attributeCode = new AttributeCode('undefined_attribute');
        $getAttributeQuery->byAttributeCode($attributeCode)->willReturn(null);

        $this->byAttributeCode($attributeCode)->shouldReturn(0);
    }
}
