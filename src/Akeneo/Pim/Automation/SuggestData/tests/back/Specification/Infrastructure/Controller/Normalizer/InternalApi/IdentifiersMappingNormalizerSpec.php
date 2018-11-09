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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_identifiers_mapping(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ): void {
        $manufacturer->getCode()->willReturn('brand');
        $model->getCode()->willReturn('mpn');
        $ean->getCode()->willReturn('ean');
        $sku->getCode()->willReturn('sku');

        $this->normalize([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ])->shouldReturn([
            'brand' => 'brand',
            'mpn' => 'mpn',
            'upc' => 'ean',
            'asin' => 'sku',
        ]);
    }

    public function it_normalizes_empty_identifiers_mapping(): void
    {
        $this->normalize([])->shouldReturn([]);
    }

    public function it_normalizes_incomplete_identifiers_mapping(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ): void {
        $manufacturer->getCode()->willReturn('brand');
        $ean->getCode()->willReturn('ean');

        $this->normalize([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ])->shouldReturn([
            'brand' => 'brand',
            'mpn' => null,
            'upc' => 'ean',
            'asin' => null,
        ]);
    }
}
