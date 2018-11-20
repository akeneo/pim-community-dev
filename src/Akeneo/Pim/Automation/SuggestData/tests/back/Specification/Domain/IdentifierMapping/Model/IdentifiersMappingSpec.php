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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingSpec extends ObjectBehavior
{
    public function let(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ): void {
        $this->beConstructedWith([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ]);
    }

    public function it_gets_identifiers($manufacturer, $model, $ean, $sku): void
    {
        $this->getIdentifiers()->shouldReturn([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ]);
    }

    public function it_gets_an_identifier($manufacturer): void
    {
        $this->getIdentifier('brand')->shouldReturn($manufacturer);
    }

    public function it_fails_to_get_an_unknown_identifier(): void
    {
        $this->getIdentifier('burger')->shouldReturn(null);
    }

    public function it_is_valid_if_mapping_is_filled(): void
    {
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_upc($ean): void
    {
        $this->beConstructedWith(['upc' => $ean]);

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_asin($sku): void
    {
        $this->beConstructedWith(['asin' => $sku]);

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_mpn_and_brand($manufacturer, $model): void
    {
        $this->beConstructedWith(['mpn' => $model, 'brand' => $manufacturer]);

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_not_valid_if_mapping_is_not_filled(): void
    {
        $this->beConstructedWith([]);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_brand($manufacturer): void
    {
        $this->beConstructedWith(['brand' => $manufacturer]);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_mpn($model): void
    {
        $this->beConstructedWith(['mpn' => $model]);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_checks_if_mapping_is_defined(): void
    {
        $this->beConstructedWith([
            'brand' => null,
            'mpn' => null,
            'upc' => null,
            'asin' => null,
        ]);

        $this->isEmpty()->shouldReturn(true);
    }
}
