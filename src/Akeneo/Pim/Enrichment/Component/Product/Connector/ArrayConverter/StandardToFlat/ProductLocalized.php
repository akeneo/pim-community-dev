<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert standard format to flat format for product with localized values.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductLocalized implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var AttributeConverterInterface */
    protected $localizer;

    /**
     * @param ArrayConverterInterface     $converter
     * @param AttributeConverterInterface $localizer
     */
    public function __construct(
        ArrayConverterInterface $converter,
        AttributeConverterInterface $localizer
    ) {
        $this->converter = $converter;
        $this->localizer = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $productStandard, array $options = [])
    {
        if (isset($productStandard['values'])) {
            $productStandard['values'] = $this->localizer->convertToLocalizedFormats($productStandard['values'], $options);
        }

        $productFlat = $this->converter->convert($productStandard, $options);

        return $productFlat;
    }
}
