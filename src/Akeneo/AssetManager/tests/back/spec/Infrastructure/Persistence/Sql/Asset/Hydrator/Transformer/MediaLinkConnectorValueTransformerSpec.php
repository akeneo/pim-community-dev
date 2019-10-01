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

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class MediaLinkConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_a_media_link_attribute(MediaLinkAttribute $mediaLinkAttribute, ImageAttribute $imageAttribute)
    {
        $this->supports($mediaLinkAttribute)->shouldReturn(true);
        $this->supports($imageAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_to_a_normalized_connector_value(MediaLinkAttribute $mediaLinkAttribute)
    {
        $this->transform([
            'data'      => 'house_front_view',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'name_designer_fingerprint',
        ], $mediaLinkAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'house_front_view',
        ]);
    }
}
