<?php

namespace Oro\Bundle\FlexibleEntityBundle\ValueConverter;

use Oro\Bundle\ImportExportBundle\ValueConverter\AbstractValueConverter;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

class OptionValueConverter extends AbstractValueConverter
{
    /**
     * @param AttributeOption $input
     * @return string|null
     */
    protected function processConversion($input)
    {
        $optionValue = $input->getOptionValue();
        if ($optionValue) {
            return $optionValue->getValue();
        }

        return null;
    }
}
