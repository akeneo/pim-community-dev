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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class OptionConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_option_attribute(
        OptionAttribute $optionAttribute,
        ImageAttribute $imageAttribute
    ) {
        $this->supports($optionAttribute)->shouldReturn(true);
        $this->supports($imageAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(OptionCode::fromString('plastic'))->willReturn(true);

        $this->transform([
            'data'      => 'plastic',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'plastic',
        ]);
    }

    function it_returns_null_if_the_option_does_not_exist_in_the_attribute(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(OptionCode::fromString('wood'))->willReturn(false);

        $this->transform([
            'data'      => 'wood',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'material_designer_fingerprint',
        ], $optionAttribute)->shouldReturn(null);
    }
}
