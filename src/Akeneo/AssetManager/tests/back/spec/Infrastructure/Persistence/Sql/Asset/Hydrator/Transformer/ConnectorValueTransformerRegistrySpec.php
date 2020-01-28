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

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerRegistry;
use PhpSpec\ObjectBehavior;

class ConnectorValueTransformerRegistrySpec extends ObjectBehavior
{
    function let(
        ConnectorValueTransformerInterface $textTransformer,
        ConnectorValueTransformerInterface $imageTransformer
    ) {
        $this->beConstructedWith([$textTransformer, $imageTransformer]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorValueTransformerRegistry::class);
    }

    function it_returns_the_transformer_that_supports_the_given_type(
        $textTransformer,
        $imageTransformer,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $textTransformer->supports($mediaFileAttribute)->willReturn(false);
        $imageTransformer->supports($mediaFileAttribute)->willReturn(true);

        $this->getTransformer($mediaFileAttribute)->shouldReturn($imageTransformer);
    }

    function it_should_throws_an_exception_if_no_transformer_supports_the_given_type(
        $textTransformer,
        $imageTransformer,
        AssetAttribute $assetAttribute
    ) {
        $textTransformer->supports($assetAttribute)->willReturn(false);
        $imageTransformer->supports($assetAttribute)->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)->during('getTransformer', [$assetAttribute]);
    }
}
