<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MetricNormalizer implements AxisValueLabelsNormalizer
{
    /**
     * @param ValueInterface $value
     * @param string         $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        return sprintf('%s %s', $value->getAmount(), $value->getUnit());
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::METRIC === $attributeType;
    }
}
