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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleValueFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalizableAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesByAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

class GetProductAttributesCodesSpec extends ObjectBehavior
{
    public function let(
        GetAttributesByTypeFromProductQueryInterface $getAttributesByTypeFromProductQuery,
        GetAttributeAsMainTitleValueFromProductIdInterface $getAttributeAsMainTitleValueFromProductId,
        GetLocalizableAttributesByTypeFromProductQueryInterface $getLocalizableAttributesByTypeFromProductQuery
    ) {
        $this->beConstructedWith($getAttributesByTypeFromProductQuery, $getAttributeAsMainTitleValueFromProductId, $getLocalizableAttributesByTypeFromProductQuery);
    }

    public function it_returns_nothing_when_there_is_no_attributes_codes(
        $getAttributesByTypeFromProductQuery
    ) {
        $getAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXTAREA)
            ->willReturn([]);

        $this->getTextarea(new ProductId(1))->shouldReturn([]);
    }

    public function it_returns_textarea_attributes_codes(
        $getAttributesByTypeFromProductQuery
    ) {
        $getAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXTAREA)
            ->willReturn(['textarea_1', 'textarea_2', 'textarea_3', 'textarea_4', 'textarea_5']);

        $this->getTextarea(new ProductId(1))->shouldReturn(['textarea_1', 'textarea_2', 'textarea_3', 'textarea_4', 'textarea_5']);
    }

    public function it_returns_text_attributes_codes(
        $getAttributesByTypeFromProductQuery
    ) {
        $getAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXT)
            ->willReturn(['text_1', 'text_2', 'text_3', 'text_4', 'text_5']);

        $this->getText(new ProductId(1))->shouldReturn(['text_1', 'text_2', 'text_3', 'text_4', 'text_5']);
    }

    public function it_returns_localizable_text_attributes_codes(
        $getLocalizableAttributesByTypeFromProductQuery
    ) {
        $getLocalizableAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXT)
            ->willReturn(['text_localizable']);

        $this->getLocalizableText(new ProductId(1))->shouldReturn(['text_localizable']);
    }

    public function it_returns_title_attribute_code(
        $getAttributeAsMainTitleValueFromProductId
    ) {
        $getAttributeAsMainTitleValueFromProductId
            ->execute(new ProductId(1))
            ->willReturn(['title_1' => '']);

        $this->getTitle(new ProductId(1))->shouldReturn(['title_1']);
    }
}
