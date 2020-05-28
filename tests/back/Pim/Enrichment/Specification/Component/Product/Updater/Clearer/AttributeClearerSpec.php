<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearer;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeClearerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AttributeClearer::class);
    }

    function it_is_an_attribute_clearer()
    {
        $this->shouldImplement(ClearerInterface::class);
    }

    function it_supports_only_attributes(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('title')->willReturn($this->buildAttribute('title'));
        $getAttributes->forCode('categories')->willReturn(null);

        $this->supportsProperty('title')->shouldReturn(true);
        $this->supportsProperty('categories')->shouldReturn(false);
    }

    function it_clears_an_attribute_value_of_a_product(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('title')->willReturn($this->buildAttribute('title'));
        $product = new Product();
        $product->setValues(new WriteValueCollection([
            ScalarValue::value('title', 'the title'),
        ]));

        $this->clear($product, 'title', ['locale' => null, 'scope' => null]);
        Assert::null($product->getValue('title'));
    }

    function it_clears_a_localizable_scopable_attribute_value_of_a_product(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('title')->willReturn($this->buildAttribute('title'));
        $product = new Product();
        $product->setValues(new WriteValueCollection([
            ScalarValue::scopableLocalizableValue('title', 'the title1', 'ecommerce', 'fr_FR'),
            ScalarValue::scopableLocalizableValue('title', 'the title2', 'print', 'fr_FR'),
            ScalarValue::scopableLocalizableValue('title', 'the title3', 'ecommerce', 'en_US'),
            ScalarValue::scopableLocalizableValue('title', 'the title4', 'print', 'en_US'),
            ScalarValue::scopableLocalizableValue('description', 'description', 'print', 'en_US'),
        ]));

        $this->clear($product, 'title', ['locale' => 'en_US', 'scope' => 'print']);
        Assert::null($product->getValue('title', 'en_US', 'print'));
        Assert::notNull($product->getValue('title', 'en_US', 'ecommerce'));
        Assert::notNull($product->getValue('title', 'fr_FR', 'ecommerce'));
        Assert::notNull($product->getValue('title', 'fr_FR', 'print'));
        Assert::notNull($product->getValue('description', 'en_US', 'print'));
    }

    function it_clears_nothing_when_the_value_does_not_exist(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('title')->willReturn($this->buildAttribute('title'));
        $product = new Product();
        $product->setValues(new WriteValueCollection([
            ScalarValue::scopableLocalizableValue('title', 'the title', 'ecommerce', 'en_US'),
            ScalarValue::scopableLocalizableValue('description', 'description', 'print', 'en_US'),
        ]));

        $this->clear($product, 'title', ['locale' => 'de_DE', 'scope' => 'print']);
        Assert::notNull($product->getValue('title', 'en_US', 'ecommerce'));
        Assert::notNull($product->getValue('description', 'en_US', 'print'));
    }

    function it_cannot_clear_if_the_property_is_not_an_attribute(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('title')->willReturn(null);
        $product = new Product();

        $this->shouldThrow(new \InvalidArgumentException('The clearer does not handle the "title" property.'))
            ->during('clear', [$product, 'title', ['locale' => null, 'scope' => null]]);
    }

    private function buildAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            AttributeTypes::BACKEND_TYPE_TEXT,
            [],
            false,
            false,
            null,
            null,
            true,
            '',
            []
        );
    }
}
