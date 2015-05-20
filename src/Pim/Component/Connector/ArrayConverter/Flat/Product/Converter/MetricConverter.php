<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

/**
 * Converts flat metric into structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricConverter extends AbstractConverter
{
    /**
     * @param FieldSplitter $fieldSplitter
     * @param array         $supportedFieldType
     */
    public function __construct(
        FieldSplitter $fieldSplitter,
        array $supportedFieldType
    ) {
        parent::__construct($fieldSplitter);
        $this->supportedFieldType = $supportedFieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($fieldNameInfo, $value)
    {
        if ('' === $value) {
            return null;
        }

        //Due to the multi column format for metrics
        if (isset($fieldNameInfo['metric_unit'])) {
            $unit = $fieldNameInfo['metric_unit'];
        } else {
            list($value, $unit) = $this->fieldSplitter->splitUnitValue($value);
        }


        $data = ['data' => (float) $value, 'unit' => $unit];

        return [$fieldNameInfo['attribute']->getCode() => [[
            'locale' => $fieldNameInfo['locale_code'],
            'scope'  => $fieldNameInfo['scope_code'],
            'data'   => $data,
        ]]];
    }
}
