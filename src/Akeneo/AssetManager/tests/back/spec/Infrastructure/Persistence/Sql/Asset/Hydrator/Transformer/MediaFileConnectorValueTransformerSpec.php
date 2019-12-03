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

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class MediaFileConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_a_media_file_attribute(TextAttribute $textAttribute, MediaFileAttribute $mediaFileAttribute)
    {
        $this->supports($mediaFileAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(MediaFileAttribute $mediaFileAttribute)
    {
        $this->transform([
            'data'      => [
                'size'             => 1024,
                'filePath'         => 'test/image_1.jpg',
                'originalFilename' => 'image_1.jpg'
            ],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'image_designer_fingerprint',
        ], $mediaFileAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'test/image_1.jpg',
        ]);
    }
}
