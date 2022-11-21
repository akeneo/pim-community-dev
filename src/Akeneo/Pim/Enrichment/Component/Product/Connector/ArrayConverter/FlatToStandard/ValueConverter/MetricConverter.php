<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

/**
 * Converts flat metric into structured one.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricConverter extends AbstractValueConverter
{
    /**
     * @param FieldSplitter $fieldSplitter
     * @param array         $supportedFieldType
     */
    public function __construct(FieldSplitter $fieldSplitter, array $supportedFieldType)
    {
        parent::__construct($fieldSplitter);

        $this->supportedFieldType = $supportedFieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $attributeFieldInfo, $value)
    {
        if ('' === $value) {
            $value = null;
        } else {
            $data = null;
            $unit = null;
            $tokens = $this->fieldSplitter->splitUnitValue($value);
            if (1 === count($tokens)) {
                /* PIM-8290: If there is only one word in the value field, this can be the unit either the amount.
                 * There can be 3 cases:
                 * 1) this is a valid amount (e.g. 12 or 12.3456)
                 * 2) this is a valid metric (e.g. 'GRAM')
                 * 3) this is an invalid string (e.g. 'foo')
                 *
                 * The case 1 is valid and won't be caught by this next regexp, and update a metric without unit.
                 * The case 2 is valid but will not imply a value update as there is no amount
                 * The case 3 will raise an invalid unit value from ValidMetric validator.
                 */
                if (preg_match('/^[A-Za-z_]+$/', $tokens[0])) {
                    $data = null;
                    $unit = $tokens[0];
                } else {
                    $data = $tokens[0];
                    $unit = null;
                }
            } else {
                $data = $tokens[0];
                $unit = $tokens[1];
            }

            if (null !== $data) {
                $data = !$attributeFieldInfo['attribute']->isDecimalsAllowed() && preg_match('|^\d+$|', $data) ?
                    (int) $data : (string) $data;
            }

            $value = ['amount' => $data, 'unit' => $unit];
        }

        return [$attributeFieldInfo['attribute']->getCode() => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $value
        ]]];
    }
}
