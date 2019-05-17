<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Field provider for reference data
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFieldProvider implements FieldProviderInterface
{
    /** @var array */
    protected $fields = [
        'pim_reference_data_simpleselect' => 'akeneo-simple-select-reference-data-field',
        'pim_reference_data_multiselect'  => 'akeneo-multi-select-reference-data-field'
    ];

    /**
     * {@inheritdoc}
     */
    public function getField($attribute)
    {
        return $this->fields[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getType(), array_keys($this->fields));
    }
}
