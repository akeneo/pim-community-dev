<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 */
class BooleanNormalizer implements AxisValueLabelsNormalizer
{
    /**
     * @param ValueInterface $value
     * @param string         $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        return (true === $value->getData() ? '1' : '0');
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }
}
