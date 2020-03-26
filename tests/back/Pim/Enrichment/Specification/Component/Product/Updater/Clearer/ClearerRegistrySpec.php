<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\FieldClearerInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearerRegistrySpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        AttributeClearerInterface $attributeClearer,
        FieldClearerInterface $fieldClearer1,
        FieldClearerInterface $fieldClearer2
    ) {
        $this->beConstructedWith($getAttributes, [
            $attributeClearer,
            $fieldClearer1,
            $fieldClearer2
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ClearerRegistry::class);
    }

    function it_is_a_clearer_registry()
    {
        $this->shouldImplement(ClearerRegistryInterface::class);
    }

    function it_returns_an_attribute_clearer(GetAttributes $getAttributes, AttributeClearerInterface $attributeClearer)
    {
        $attribute = $this->buildAttribute('title');
        $getAttributes->forCode('title')->willReturn($attribute);
        $attributeClearer->supportsAttributeCode('title')->willReturn(true);

        $this->getClearer('title')->shouldReturn($attributeClearer);
    }

    function it_returns_a_field_clearer(
        GetAttributes $getAttributes,
        FieldClearerInterface $fieldClearer1,
        FieldClearerInterface $fieldClearer2
    ) {
        $getAttributes->forCode('categories')->willReturn(null);
        $fieldClearer1->supportsField('categories')->willReturn(false);
        $fieldClearer2->supportsField('categories')->willReturn(true);

        $this->getClearer('categories')->shouldReturn($fieldClearer2);
    }

    function it_returns_null_for_an_attribute_when_no_clearer_is_found(
        GetAttributes $getAttributes,
        AttributeClearerInterface $attributeClearer
    ) {
        $attribute = $this->buildAttribute('title');
        $getAttributes->forCode('title')->willReturn($attribute);
        $attributeClearer->supportsAttributeCode('title')->willReturn(false);

        $this->getClearer('title')->shouldReturn(null);
    }

    function it_returns_null_for_a_field_when_no_clearer_is_found(
        GetAttributes $getAttributes,
        FieldClearerInterface $fieldClearer1,
        FieldClearerInterface $fieldClearer2
    ) {
        $getAttributes->forCode('categories')->willReturn(null);
        $fieldClearer1->supportsField('categories')->willReturn(false);
        $fieldClearer2->supportsField('categories')->willReturn(false);

        $this->getClearer('categories')->shouldReturn(null);
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
