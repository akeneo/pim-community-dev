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
    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $groupClassName;

    /**
     * @param string $groupClassName
     * @param string $dataClass
     */
    public function __construct($groupClassName, $dataClass)
    {
        $this->groupClassName = $groupClassName;
        $this->dataClass      = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'group',
            'entity',
            [
                'class'    => $this->groupClassName,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => $options['groups'],
                'select2'  => true,
                'empty_value'  => '',
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
                'data_class' => $this->dataClass,
                'groups' => []
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
