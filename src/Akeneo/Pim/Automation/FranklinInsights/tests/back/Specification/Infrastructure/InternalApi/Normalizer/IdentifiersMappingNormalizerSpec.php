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
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_identifiers_mapping(): void
    {
        $identifiersMapping = new IdentifiersMapping([]);
        $identifiersMapping
            ->map('brand', new AttributeCode('brand'))
            ->map('mpn', new AttributeCode('mpn'))
            ->map('upc', new AttributeCode('ean'))
            ->map('asin', new AttributeCode('sku'));

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

    public function it_normalizes_incomplete_identifiers_mapping(): void
    {
        $identifiersMapping = new IdentifiersMapping([]);
        $identifiersMapping
            ->map('brand', new AttributeCode('brand'))
            ->map('mpn', null)
            ->map('upc', new AttributeCode('ean'))
            ->map('asin', null);

        $this->normalize($identifiersMapping)->shouldReturn([
            'brand' => 'brand',
            'mpn' => null,
            'upc' => 'ean',
            'asin' => null,
        ]);
    }
}
