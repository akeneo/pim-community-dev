<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class OptionCollectionConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_option_attribute(
        OptionCollectionAttribute $optionCollectionAttribute,
        ImageAttribute $imageAttribute
    ) {
        $this->supports($optionCollectionAttribute)->shouldReturn(true);
        $this->supports($imageAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(
        OptionCollectionAttribute $optionCollectionAttribute,
        AttributeOption $attributeOption1,
        AttributeOption $attributeOption2,
        AttributeOption $attributeOption3
    ) {
        $optionCollectionAttribute->getAttributeOptions()->willReturn([$attributeOption1, $attributeOption2, $attributeOption3]);
        $attributeOption1->getCode()->willReturn(OptionCode::fromString('metal'));
        $attributeOption2->getCode()->willReturn(OptionCode::fromString('plastic'));
        $attributeOption3->getCode()->willReturn(OptionCode::fromString('wood'));

        $this->transform([
            'data'      => ['plastic', 'metal'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionCollectionAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['plastic', 'metal'],
        ]);
    }

    function it_removes_options_that_do_not_exist_in_the_attribute(
        OptionCollectionAttribute $optionCollectionAttribute,
        AttributeOption $attributeOption1,
        AttributeOption $attributeOption2
    ) {
        $optionCollectionAttribute->getAttributeOptions()->willReturn([$attributeOption1, $attributeOption2]);
        $attributeOption1->getCode()->willReturn(OptionCode::fromString('metal'));
        $attributeOption2->getCode()->willReturn(OptionCode::fromString('plastic'));

        $this->transform([
            'data'      => ['plastic', 'wood', 'metal'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionCollectionAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['plastic', 'metal'],
        ]);
    }

    function it_returns_null_if_no_options_exist_in_the_attribute(
        OptionCollectionAttribute $optionCollectionAttribute,
        AttributeOption $attributeOption1,
        AttributeOption $attributeOption2
    ) {
        $optionCollectionAttribute->getAttributeOptions()->willReturn([$attributeOption1, $attributeOption2]);
        $attributeOption1->getCode()->willReturn(OptionCode::fromString('metal'));
        $attributeOption2->getCode()->willReturn(OptionCode::fromString('plastic'));

        $this->transform([
            'data'      => ['wood'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionCollectionAttribute)->shouldReturn(null);
    }
}
