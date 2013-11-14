<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionValueType as FlexibleAttributeOptionValueType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for option value attribute form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOptionValueType extends FlexibleAttributeOptionValueType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue'
            )
        );
    }
}
