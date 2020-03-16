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

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\PriceCollectionValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use PhpSpec\ObjectBehavior;

class PriceCollectionValueStringifierSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['type1', 'type2']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(PriceCollectionValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['type1', 'type2']);
    }

    function it_stringifies_a_price_value()
    {
        $priceCollection = new PriceCollection();
        $priceCollection->add(new ProductPrice(10.00, 'EUR'));
        $priceCollection->add(new ProductPrice(15.90, 'USD'));
        $value = PriceCollectionValue::value('price', $priceCollection);

        $this->stringify($value)->shouldBe('10 EUR, 15.90 USD');
    }

    function it_stringifies_a_price_value_with_specific_currency()
    {
        $priceCollection = new PriceCollection();
        $priceCollection->add(new ProductPrice(10.00, 'EUR'));
        $priceCollection->add(new ProductPrice(15.90, 'USD'));
        $value = PriceCollectionValue::value('price', $priceCollection);

        $this->stringify($value, ['currency' => 'USD'])->shouldBe('15.90 USD');
    }
}
