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

class MultiSelectSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_multiselect'], $getExistingAttributeOptionsWithValues);

        $attribute = $this->createAttribute('pim_catalog_multiselect');
        $this->supports(['type' => 'code'], $attribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $attribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $attribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_multiselect'], $getExistingAttributeOptionsWithValues);
        $attribute = $this->createAttribute('pim_catalog_multiselect');
        $value->getData()->willReturn(['code1', 'code2']);

        $this->applySelection(['type' => 'code', 'separator' => ','], $attribute, $value)->shouldReturn('code1,code2');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->beConstructedWith(['pim_catalog_multiselect'], $getExistingAttributeOptionsWithValues);
        $attribute = $this->createAttribute('pim_catalog_multiselect');
        $value->getData()->willReturn(['code1', 'code2', 'code3']);
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            [
                'description.code1',
                'description.code2',
                'description.code3',
            ]
        )->willReturn(
            [
                'description.code1' => [
                    'fr_FR' => 'label1',
                    'en_US' => 'another_label',
                ],
                'description.code2' => [
                    'fr_FR' => 'label2',
                    'en_US' => 'a_label',
                ],
                'description.code3' => [
                    'en_US' => 'a_label_even_better',
                ],
            ]
        );

        $this->applySelection(
            ['type' => 'label', 'locale' => 'fr_FR', 'separator' => ';'],
            $attribute,
            $value
        )->shouldReturn('label1;label2;[code3]');
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
