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

class TextConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_a_text_attribute(TextAttribute $textAttribute, MediaFileAttribute $mediaFileAttribute)
    {
        $this->supports($textAttribute)->shouldReturn(true);
        $this->supports($mediaFileAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_to_a_normalized_connector_value(TextAttribute $textAttribute)
    {
        $this->transform([
            'data'      => 'Starck',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'name_designer_fingerprint',
        ], $textAttribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'Starck',
        ]);
    }
}
