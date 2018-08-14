<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

/**
 * Ajax reference data type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxReferenceDataType extends AjaxEntityType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_ajax_reference_data';
    }
}
