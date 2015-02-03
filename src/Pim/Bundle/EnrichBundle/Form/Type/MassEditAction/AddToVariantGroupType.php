<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Add to groups mass action form type
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'group',
            'entity',
            array(
                'class'    => 'Pim\\Bundle\\CatalogBundle\\Entity\\Group',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => $options['groups'],
                'select2'  => true,
                'empty_value'  => '',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\AddToVariantGroup',
                'groups' => array()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_add_to_variant_group';
    }
}
