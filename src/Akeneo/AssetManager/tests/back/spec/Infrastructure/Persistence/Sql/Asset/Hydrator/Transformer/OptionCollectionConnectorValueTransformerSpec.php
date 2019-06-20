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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
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
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('metal'))->willReturn(true);
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('plastic'))->willReturn(true);

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
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('metal'))->willReturn(true);
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('plastic'))->willReturn(true);
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('wood'))->willReturn(false);

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
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString('wood'))->willReturn(false);

        $this->transform([
            'data'      => ['wood'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionCollectionAttribute)->shouldReturn(null);
    }
}
