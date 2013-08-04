<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for AttributeGroup form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('code');

        $builder->add(
            'name',
            'pim_translatable_field',
            array(
                'field'             => 'name',
                'translation_class' => 'Pim\\Bundle\\ProductBundle\\Entity\\AttributeGroupTranslation',
                'entity_class'      => 'Pim\\Bundle\\ProductBundle\\Entity\\AttributeGroup',
                'property_path'     => 'translations'
            )
        );

        $builder->add('sort_order', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\AttributeGroup'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_attribute_group';
    }
}
