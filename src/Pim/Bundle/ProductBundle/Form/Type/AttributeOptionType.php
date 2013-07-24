<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType as FlexibleAttributeOptionType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\ProductBundle\Form\Type\AttributeOptionValueType as ProductAttributeOptionValueType;

/**
 * Type for option attribute form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class AttributeOptionType extends FlexibleAttributeOptionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addFieldIsDefault($builder);
    }

    /**
     * Add options values to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldOptionValues(FormBuilderInterface $builder)
    {
        $builder->add(
            'optionValues',
            'collection',
            array(
                'type'         => new ProductAttributeOptionValueType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
    }

    /**
     * Add isDefault field to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldIsDefault(FormBuilderInterface $builder)
    {
        $builder->add('default', 'hidden');
    }

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
