<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;

/**
 * Convert an entity with values from Flat to Standard format with delocalized values.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesDelocalized implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var AttributeConverterInterface */
    protected $delocalizer;

    /**
     * @param ArrayConverterInterface     $converter
     * @param AttributeConverterInterface $delocalizer
     */
    public function __construct(ArrayConverterInterface $converter, AttributeConverterInterface $delocalizer)
    {
        $this->converter = $converter;
        $this->delocalizer = $delocalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $standardizedItem = $this->converter->convert($item, $options);

        if (isset($standardizedItem['values'])) {
            $standardizedItem['values'] = $this->delocalizer->convertToDefaultFormats(
                $standardizedItem['values'],
                $options
            );
        }

        $violations = $this->delocalizer->getViolations();

        if ($violations->count() > 0) {
            throw new DataArrayConversionException(
                'An error occurred during the delocalization of the entity with values.',
                0,
                null,
                $violations
            );
        }

        return $standardizedItem;
    }
}
