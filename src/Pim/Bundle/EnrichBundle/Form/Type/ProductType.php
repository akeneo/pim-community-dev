<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);
        $this->addDynamicAttributesFields($builder, $options);
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'pim_enrich_localized_collection',
            array(
                'type'               => 'pim_product_value',
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
                'currentLocale'      => $options['currentLocale'],
                'comparisonLocale'   => $options['comparisonLocale'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'currentLocale'    => null,
                'comparisonLocale' => null,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product';
    }
}
