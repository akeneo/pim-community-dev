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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerRegistry;
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
        ImageAttribute $imageAttribute
    ) {
        $textTransformer->supports($imageAttribute)->willReturn(false);
        $imageTransformer->supports($imageAttribute)->willReturn(true);

        $this->getTransformer($imageAttribute)->shouldReturn($imageTransformer);
    }

    function it_should_throws_an_exception_if_no_transformer_supports_the_given_type(
        $textTransformer,
        $imageTransformer,
        RecordAttribute $recordAttribute
    ) {
        $textTransformer->supports($recordAttribute)->willReturn(false);
        $imageTransformer->supports($recordAttribute)->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)->during('getTransformer', [$recordAttribute]);
    }
}
