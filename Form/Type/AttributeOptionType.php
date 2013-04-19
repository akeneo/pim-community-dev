<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType as FlexibleAttributeOptionType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for option attribute form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class AttributeOptionType extends FlexibleAttributeOptionType;
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\AttributeOption'
            )
        );
    }
}

