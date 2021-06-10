<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

class SimpleSelectSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_simpleselect'], $getExistingAttributeOptionsWithValues);

        $simpleSelectAttribute = $this->createAttribute('pim_catalog_simpleselect');
        $this->supports(['type' => 'code'], $simpleSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $simpleSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $simpleSelectAttribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_simpleselect'], $getExistingAttributeOptionsWithValues);
        $simpleSelectAttribute = $this->createAttribute('pim_catalog_simpleselect');
        $value->getData()->willReturn('the_code');

        $this->applySelection(['type' => 'code'], $simpleSelectAttribute, $value)->shouldReturn('the_code');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_simpleselect'], $getExistingAttributeOptionsWithValues);
        $simpleSelectAttribute = $this->createAttribute('pim_catalog_simpleselect');
        $value->getData()->willReturn('the_code');
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['description.the_code'])
            ->willReturn([
                'description.the_code' => ['fr_FR' => 'Le label', 'en_US' => 'The label']
            ]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $simpleSelectAttribute, $value)->shouldReturn('Le label');
    }

    public function it_selects_the_code_when_label_is_undefined(
        ValueInterface $value,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_simpleselect'], $getExistingAttributeOptionsWithValues);
        $simpleSelectAttribute = $this->createAttribute('pim_catalog_simpleselect');
        $value->getData()->willReturn('the_code');
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['description.the_code'])
            ->willReturn([
                'description.the_code' => ['en_US' => 'The label']
            ]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $simpleSelectAttribute, $value)->shouldReturn('[the_code]');
    }

    private function createAttribute(string $attributeType): Attribute
    {
        return new Attribute(
            'description',
            $attributeType,
            [],
            false,
            false,
            null,
            null,
            null,
            'bool',
            []
        );
    }
}
