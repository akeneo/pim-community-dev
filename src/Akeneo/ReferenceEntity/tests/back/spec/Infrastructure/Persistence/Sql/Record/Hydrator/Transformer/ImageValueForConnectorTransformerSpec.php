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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ValueForConnectorTransformerInterface;
use PhpSpec\ObjectBehavior;

class ImageValueForConnectorTransformerSpec extends ObjectBehavior
{
    function it_is_a_value_for_connector_transformer()
    {
        $this->shouldImplement(ValueForConnectorTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_image_attribute(TextAttribute $textAttribute, ImageAttribute $imageAttribute)
    {
        $this->supports($imageAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_value_for_connector_from_a_raw_value_without_missing_data()
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
        ])->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'test/image_1.jpg',
        ]);
    }

    function it_transforms_a_value_for_connector_from_a_raw_value_with_missing_data()
    {
        $this->transform([
            'attribute' => 'image_designer_fingerprint',
        ])->shouldReturn([
            'locale'  => null,
            'channel' => null,
            'data'    => null,
        ]);
    }
}
