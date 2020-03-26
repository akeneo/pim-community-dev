<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearer;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeClearerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AttributeClearer::class);
    }

    function it_is_an_attribute_clearer()
    {
        $this->shouldImplement(AttributeClearerInterface::class);
    }

    function it_supports_all_attributes()
    {
        $this->supportsAttributeCode('title')->shouldReturn(true);
    }

    function it_clears_an_attribute_value_of_a_product()
    {
        $product = new Product();
        $product->setValues(new WriteValueCollection([
            ScalarValue::value('title', 'the title'),
        ]));

        $this->clear($product, 'title', ['locale' => null, 'scope' => null]);
        Assert::null($product->getValue('title'));
    }

    function it_clears_a_localizable_scopable_attribute_value_of_a_product()
    {
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

    function it_clears_nothing_when_the_value_does_not_exist()
    {
        $product = new Product();
        $product->setValues(new WriteValueCollection([
            ScalarValue::scopableLocalizableValue('title', 'the title', 'ecommerce', 'en_US'),
            ScalarValue::scopableLocalizableValue('description', 'description', 'print', 'en_US'),
        ]));

        $this->clear($product, 'title', ['locale' => 'de_DE', 'scope' => 'print']);
        Assert::notNull($product->getValue('title', 'en_US', 'ecommerce'));
        Assert::notNull($product->getValue('description', 'en_US', 'print'));
    }
}
