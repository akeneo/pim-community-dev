<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\BooleanTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Webmozart\Assert\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanNormalizer implements AxisValueLabelsNormalizer
{
    public function __construct(
        private BooleanTranslator $translator
    ) {
    }

    public function normalize(ValueInterface $value, string $locale): string
    {
        $formattedValue = (true === $value->getData()) ? '1' : '0';
        return $this->translator->translate('', [], [$formattedValue], $locale)[0];
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }
}
