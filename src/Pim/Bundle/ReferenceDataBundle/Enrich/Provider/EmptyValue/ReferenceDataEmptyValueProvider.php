<?php

namespace Pim\Bundle\ReferenceDataBundle\Enrich\Provider\EmptyValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;

/**
 * EmptyValue provider for reference data attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataEmptyValueProvider implements EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute)
    {
        return 'pim_reference_data_multiselect' === $attribute->getAttributeType() ? [] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            null !== $element->getReferenceDataName() &&
            '' !== $element->getReferenceDataName();
    }
}
