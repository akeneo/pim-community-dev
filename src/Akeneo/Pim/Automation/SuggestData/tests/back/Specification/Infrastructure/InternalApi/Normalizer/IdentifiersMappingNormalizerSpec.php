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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
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

        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping
            ->map('brand', $manufacturer->getWrappedObject())
            ->map('mpn', $model->getWrappedObject())
            ->map('upc', $ean->getWrappedObject())
            ->map('asin', $sku->getWrappedObject());

        $this->normalize($identifiersMapping)->shouldReturn([
            'brand' => 'brand',
            'mpn' => 'mpn',
            'upc' => 'ean',
            'asin' => 'sku',
        ]);
    }

    public function it_normalizes_empty_identifiers_mapping(): void
    {
        $this->normalize(new IdentifiersMapping())->shouldReturn([
            'brand' => null,
            'mpn' => null,
            'upc' => null,
            'asin' => null,
        ]);
    }

    public function it_normalizes_incomplete_identifiers_mapping(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ): void {
        $manufacturer->getCode()->willReturn('brand');
        $ean->getCode()->willReturn('ean');

        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping
            ->map('brand', $manufacturer->getWrappedObject())
            ->map('mpn', $model->getWrappedObject())
            ->map('upc', $ean->getWrappedObject())
            ->map('asin', $sku->getWrappedObject());

        $this->normalize($identifiersMapping)->shouldReturn([
            'brand' => 'brand',
            'mpn' => null,
            'upc' => 'ean',
            'asin' => null,
        ]);
    }
}
