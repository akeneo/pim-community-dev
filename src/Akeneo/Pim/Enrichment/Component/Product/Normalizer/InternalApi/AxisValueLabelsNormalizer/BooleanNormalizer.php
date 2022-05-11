<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanNormalizer implements AxisValueLabelsNormalizer
{
    public function __construct(
        private LabelTranslatorInterface $labelTranslator
    ) {
    }

    public function normalize(ValueInterface $value, string $locale): string
    {
        if (true === $value->getData()) {
            return $this->labelTranslator->translate(
                'pim_common.yes',
                $locale,
                sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
            );
        }
        return $this->labelTranslator->translate(
            'pim_common.no',
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
        );
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }
}
