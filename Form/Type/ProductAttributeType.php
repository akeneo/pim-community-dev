<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeType extends AttributeType
{

    /**
     * {@inheritdoc}
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
        // use custom scope notion pofor product
        $builder->add(
            'scopable',
            'choice',
            array(
                'choices' => array(
                    0 => 'Global',
                    1 =>'Channel'
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addFieldName($builder);

        $this->addFieldDescription($builder);

        $this->addFieldVariantBehavior($builder);
    }

    protected function addFieldName(FormBuilderInterface $builder)
    {
        $builder->add('name');
    }

    /**
     * Add a field for description
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDescription(FormBuilderInterface $builder)
    {
        $builder->add('description', 'textarea');
    }

    /**
     * Add a field variant behavior
     * @param FormBuilderInterface $builder
     */
    protected function addFieldVariantBehavior(FormBuilderInterface $builder)
    {
        $builder->add(
            'variant',
            'choice',
            array(
                'choices' => array(
                    0 => 'Always override',
                    1 => 'A selection of variants',
                    2 => 'Ask'
                )
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
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductAttribute'
            )
        );
    }
}
