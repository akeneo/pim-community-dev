<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter as BaseFieldSplitter;

/**
 * Field splitter dedicated to the family variant column sorter.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FieldSplitter extends BaseFieldSplitter
{
    /**
     * {@inheritdoc}
     */
    public function splitFieldName($field): array
    {
        if (1 === preg_match('/variant-axes/', $field) || 1 === preg_match('/variant-attributes/', $field)) {
            return explode('_', $field);
        }

        return parent::splitFieldName($field);
    }
}
