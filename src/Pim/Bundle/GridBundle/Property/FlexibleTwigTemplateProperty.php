<?php

namespace Pim\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

/**
 * Override of TwigTemplateProperty for flexible entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleTwigTemplateProperty extends TwigTemplateProperty
{
    /**
     * {@inheritdoc}
     *
     * Override get value method to catch LogicException in case of no value for the row
     */
    public function getValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->field->getFieldName());
        } catch (\LogicException $e) {
            // default value if there is no flexible attribute
            $value = null;
        }

        $context = array_merge(
            $this->context,
            array(
                'field'  => $this->field,
                'record' => $record,
                'value'  => $value,
            )
        );

        return $this->getTemplate()->render($context);
    }
}
