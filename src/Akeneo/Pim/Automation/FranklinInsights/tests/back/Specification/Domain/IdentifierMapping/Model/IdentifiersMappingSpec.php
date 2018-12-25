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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifierMapping;
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
    }

    public function it_gets_identifiers($manufacturer, $model, $ean, $sku): void
    {
        $this->map('brand', $manufacturer);
        $this->map('mpn', $model);
        $this->map('upc', $ean);
        $this->map('asin', $sku);

        $this->getMapping()->shouldBeLike([
            'brand' => new IdentifierMapping('brand', $manufacturer->getWrappedObject()),
            'mpn' => new IdentifierMapping('mpn', $model->getWrappedObject()),
            'upc' => new IdentifierMapping('upc', $ean->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $sku->getWrappedObject()),
        ]);
    }

    public function it_is_valid_if_mapping_is_filled($manufacturer, $model, $ean, $sku): void
    {
        $this->map('brand', $manufacturer);
        $this->map('mpn', $model);
        $this->map('upc', $ean);
        $this->map('asin', $sku);

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_upc($ean): void
    {
        $this->map('upc', $ean);
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_asin($sku): void
    {
        $this->map('asin', $sku);
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_mpn_and_brand($manufacturer, $model): void
    {
        $this->map('brand', $manufacturer);
        $this->map('mpn', $model);

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_not_valid_if_mapping_is_not_filled(): void
    {
        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_brand($manufacturer): void
    {
        $this->map('brand', $manufacturer);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_mpn($model): void
    {
        $this->map('mpn', $model);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_checks_if_mapping_is_defined($sku): void
    {
        $this->isEmpty()->shouldReturn(true);

        $this->map('asin', $sku);
        $this->isEmpty()->shouldReturn(false);

        $this->map('asin', null);
        $this->isEmpty()->shouldReturn(true);
    }
}
