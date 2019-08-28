<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanNormalizer implements AxisValueLabelsNormalizer
{
    public function normalize(ValueInterface $value, string $locale): string
    {
        return true === $value->getData() ? '1' : '0';
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }
}
