<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class NumberConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_a_number_attribute(NumberAttribute $numberAttribute, ImageAttribute $imageAttribute)
    {
        $this->supports($numberAttribute)->shouldReturn(true);
        $this->supports($imageAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_to_a_normalized_connector_value(NumberAttribute $numberAttribute)
    {
        $this->transform([
            'data'      => '42.5',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'age_designer_fingerprint',
        ], $numberAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => '42.5',
        ]);
    }
}
