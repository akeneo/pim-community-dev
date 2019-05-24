<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PriceCollectionValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ReadValueFactory::class);
    }

    public function it_supports_price_collection_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::PRICE_COLLECTION);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike($priceCollection);
    }

    public function it_creates_a_localizable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike($priceCollection);
    }

    public function it_creates_a_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike($priceCollection);
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike($priceCollection);
    }

    public function it_sorts_the_result()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, null, [['amount' => 5, 'currency' => 'USD'], ['amount' => 5, 'currency' => 'EUR']]);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike($priceCollection);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::PRICE_COLLECTION, [], $isLocalizable, $isScopable, null);
    }
}
