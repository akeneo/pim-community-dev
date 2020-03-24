<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\FieldClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyClearer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyClearerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ClearerRegistryInterface $clearerRegistry)
    {
        $this->beConstructedWith($getAttributes, $clearerRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(PropertyClearer::class);
    }

    function it_is_a_property_clearer()
    {
        $this->shouldImplement(PropertyClearerInterface::class);
    }

    function it_clears_a_product_attribute_value(
        ClearerRegistryInterface $clearerRegistry,
        GetAttributes $getAttributes,
        AttributeClearerInterface $attributeClearer
    ) {
        $product = new Product();
        $clearerRegistry->getClearer('title')->willReturn($attributeClearer);
        $attribute = $this->buildAttribute('title');
        $getAttributes->forCode('title')->willReturn($attribute);

        $attributeClearer->clear($product, $attribute, ['locale' => 'en_US', 'scope' => 'ecommerce'])
            ->shouldBeCalled();

        $this->clear($product, 'title', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_clears_a_product_field(
        ClearerRegistryInterface $clearerRegistry,
        FieldClearerInterface $fieldClearer
    ) {
        $product = new Product();
        $clearerRegistry->getClearer('categories')->willReturn($fieldClearer);
        $fieldClearer->clear($product, 'categories', ['option1' => 'value1'])
            ->shouldBeCalled();

        $this->clear($product, 'categories', ['option1' => 'value1']);
    }

    function it_fails_when_no_clearer_is_found(ClearerRegistryInterface $clearerRegistry)
    {
        $product = new Product();
        $clearerRegistry->getClearer('unknown')->willReturn(null);

        $this->shouldThrow(UnknownPropertyException::class)
            ->during('clear', [$product, 'unknown', ['option1' => 'value1']]);
    }

    function it_fails_when_clearer_is_not_handled(
        ClearerRegistryInterface $clearerRegistry,
        ClearerInterface $clearer
    ) {
        $product = new Product();
        $clearerRegistry->getClearer('title')->willReturn($clearer);

        $this->shouldThrow(UnknownPropertyException::class)
            ->during('clear', [$product, 'title', ['option1' => 'value1']]);
    }

    function it_fails_when_attribute_is_not_found(
        ClearerRegistryInterface $clearerRegistry,
        GetAttributes $getAttributes,
        AttributeClearerInterface $attributeClearer
    ) {
        $product = new Product();
        $attribute = $this->buildAttribute('title');

        $clearerRegistry->getClearer('title')->willReturn($attributeClearer);
        $getAttributes->forCode('title')->willReturn(null);

        $this->shouldThrow(ResourceNotFoundException::class)
            ->during('clear', [$product, 'title', ['option1' => 'value1']]);
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
            true,
            '',
            []
        );
    }
}
