<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Multi options (checkbox) attribute type
 */
class OptionMultiCheckboxType extends AbstractOptionType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['expanded'] = true;
        $options['multiple'] = true;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_multicheckbox';
    }
}
