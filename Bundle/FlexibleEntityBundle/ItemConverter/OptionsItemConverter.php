<?php

namespace Oro\Bundle\FlexibleEntityBundle\ItemConverter;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\ImportExportBundle\ItemConverter\AbstractItemConverter;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

class OptionsItemConverter extends AbstractItemConverter
{
    /**
     * @param string $property
     * @param array $input
     * @return array
     */
    protected function processConversion($property, array $input)
    {
        /** @var $options Collection */
        $options = $input[$property];
        unset($input[$property]);

        /** @var $option AttributeOption */
        foreach ($options as $option) {
            $option->getOptionValue()->getValue();
        }

        // TODO: Implement processConversion() method.
    }
}
