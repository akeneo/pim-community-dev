<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Simple options (radio) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionSimpleRadioType extends AbstractOptionType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['expanded'] = true;
        $options['multiple'] = false;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_simpleradio';
    }
}
