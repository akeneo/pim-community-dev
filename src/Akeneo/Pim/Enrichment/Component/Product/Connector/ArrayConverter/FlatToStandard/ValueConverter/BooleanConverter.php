<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Converts boolean value into structured one.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanConverter extends AbstractValueConverter
{
    public function __construct(
        FieldSplitter $fieldSplitter,
        array $supportedFieldType,
        private TranslatorInterface $translator
    ) {
        parent::__construct($fieldSplitter);

        $this->supportedFieldType = $supportedFieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $attributeFieldInfo, $value)
    {
        if (in_array($value, ['1', '0'])) {
            $data = (bool) $value;
        } elseif ('' === $value || null === $value) {
            $data = null;
        } elseif (!\is_bool($value)) {
            throw new DataArrayConversionException($this->translator->trans(
                'pim_catalog.constraint.boolean.boolean_value_is_required_in_import',
                [
                    '{{ attribute_code }}' => $attributeFieldInfo['attribute']->getCode(),
                    '{{ given_type }}' => gettype($value),
                ],
                'validators'
            ));
        } else {
            $data = $value;
        }

        return [$attributeFieldInfo['attribute']->getCode() => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $data,
        ]]];
    }
}
