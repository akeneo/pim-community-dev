<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

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
            $tokens = $this->fieldSplitter->splitUnitValue($value);
            $data = isset($tokens[0]) ? $tokens[0] : null;
            $unit = isset($tokens[1]) ? $tokens[1] : null;
            $value = ['data' => $data, 'unit' => $unit];
        }

        return [$attributeFieldInfo['attribute']->getCode() => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $value
        ]]];
    }
}
