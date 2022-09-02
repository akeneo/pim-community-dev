<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Query\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class GetIdentifierAttributeCodeSpec extends ObjectBehavior
{
    private Attribute $identifierAttribute;

    public function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
        $this->identifierAttribute = new Attribute(
            'sku',
            'pim_catalog_identifier',
            [],
            false,
            false,
            null,
            null,
            null,
            'pim_catalog_identifier',
            []
        );
    }

    public function it_returns_identifier_attribute_code(GetAttributes $getAttributes): void
    {
        $getAttributes->forType('pim_catalog_identifier')->willReturn([$this->identifierAttribute]);

        $this->execute()->shouldReturn('sku');
    }

    public function it_caches_the_identifier_attribute_code(GetAttributes $getAttributes): void
    {
        $getAttributes->forType('pim_catalog_identifier')
            ->willReturn([$this->identifierAttribute])
            ->shouldBeCalledOnce();

        $this->execute()->shouldReturn('sku');
        $this->execute()->shouldReturn('sku');
        $this->execute()->shouldReturn('sku');
        $this->execute()->shouldReturn('sku');
    }

    public function it_throws_when_no_identifier_attribute_is_found(GetAttributes $getAttributes): void
    {
        $getAttributes->forType('pim_catalog_identifier')->willReturn([]);

        $this->shouldThrow(\RuntimeException::class)->during('execute');
    }
}
