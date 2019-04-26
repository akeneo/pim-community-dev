<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface AxisValueLabelsNormalizer
{
    public function normalize(ValueInterface $value, string $locale): string;

    public function supports(string $attributeType): bool;
}
