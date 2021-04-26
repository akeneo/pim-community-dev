<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;

/**
 * Converts text value into structured one.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextConverter extends AbstractValueConverter
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
        $code = $attributeFieldInfo['attribute']->getCode();

        if ($value instanceof \DateTimeInterface) {
            throw new BusinessArrayConversionException("Can not convert cell  {$code} with date format to attribute of type text", "pim_import_export.notification.export.warnings.xlsx_cell_date_to_text_conversion_error", [$code]);
        }
        if ('' !== $value) {
            $data = (string) $value;
        } else {
            $data = null;
        }

        return [$code => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $data,
        ]]];
    }
}
