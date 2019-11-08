<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_price_collection_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::PRICE_COLLECTION);
    }

    public function it_does_not_support_null()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->shouldBeLike(PriceCollectionValue::scopableLocalizableValue('an_attribute', $priceCollection, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->shouldBeLike(PriceCollectionValue::localizableValue('an_attribute', $priceCollection, 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->shouldBeLike(PriceCollectionValue::scopableValue('an_attribute', $priceCollection, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $value->shouldBeLike(PriceCollectionValue::value('an_attribute', $priceCollection));
    }

    public function it_sorts_the_result()
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, [['amount' => 5, 'currency' => 'USD'], ['amount' => 5, 'currency' => 'EUR']]);
        $value->shouldBeLike(PriceCollectionValue::value('an_attribute', $priceCollection));
    }

    public function it_throws_an_exception_if_it_is_not_an_array_of_amount_currency()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [$this->getAttribute(false, false), null, null, new \stdClass()]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::PRICE_COLLECTION, [], $isLocalizable, $isScopable, null, false, 'prices', []);
    }
}
