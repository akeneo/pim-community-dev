<?php

namespace Akeneo\Pim\Structure\Component\AttributeType;

/**
 * Reference data simple options (select) attribute type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleSelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_reference_data_simpleselect';
    }
}
