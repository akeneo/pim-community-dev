<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAttributeFieldExtractor;

/**
 * Converts flat numbers value into structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberConverter extends AbstractConverter
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

        $data = (float) $value;

        return [$fieldNameInfo['attribute']->getCode() => [[
            'locale' => $fieldNameInfo['locale_code'],
            'scope'  => $fieldNameInfo['scope_code'],
            'data'   => $data,
        ]]];
    }
}
