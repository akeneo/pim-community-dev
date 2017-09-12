<?php

namespace Pim\Component\Connector\ArrayConverter;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FieldSplitter
{
    /**
     * Split a field name:
     * 'description-en_US-mobile' => ['description', 'en_US', 'mobile']
     *
     * @param string $field Raw field name
     *
     * @return string[]
     */
    public function splitFieldName($field): array
    {
        return '' === $field ? [] : explode(AttributeColumnInfoExtractor::FIELD_SEPARATOR, $field);
    }
}
