<?php

namespace Pim\Bundle\CatalogBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type of the ChangeFamily operation
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamilyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'family',
            'entity',
            [
                'class'       => 'PimCatalogBundle:Family',
                'empty_value' => 'None',
                'required'    => false,
                'select2'     => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\\Bundle\\CatalogBundle\\MassEditAction\\ChangeFamily'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_mass_change_family';
    }
}
