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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_identifiers_mapping(
        Attribute $manufacturer,
        Attribute $model,
        Attribute $ean,
        Attribute $sku
    ): void {
        $manufacturer->getCode()->willReturn(new AttributeCode('brand'));
        $model->getCode()->willReturn(new AttributeCode('mpn'));
        $ean->getCode()->willReturn(new AttributeCode('ean'));
        $sku->getCode()->willReturn(new AttributeCode('sku'));

        $identifiersMapping = new IdentifiersMapping([]);
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
        $this->normalize(new IdentifiersMapping([]))->shouldReturn([
            'brand' => null,
            'mpn' => null,
            'upc' => null,
            'asin' => null,
        ]);
    }

    public function it_normalizes_incomplete_identifiers_mapping(
        Attribute $manufacturer,
        Attribute $ean
    ): void {
        $manufacturer->getCode()->willReturn(new AttributeCode('brand'));
        $ean->getCode()->willReturn(new AttributeCode('ean'));

        $identifiersMapping = new IdentifiersMapping([]);
        $identifiersMapping
            ->map('brand', $manufacturer->getWrappedObject())
            ->map('mpn', null)
            ->map('upc', $ean->getWrappedObject())
            ->map('asin', null);

        $this->normalize($identifiersMapping)->shouldReturn([
            'brand' => 'brand',
            'mpn' => null,
            'upc' => 'ean',
            'asin' => null,
        ]);
    }
}
