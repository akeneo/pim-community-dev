<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BindGroupProductsSubscriber;

/**
 * Type for group form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');

        $this->addLabelField($builder);

        $this->addProductsField($builder);

        $builder
            ->addEventSubscriber(new BindGroupProductsSubscriber());
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\GroupTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\Group',
                'property_path'     => 'translations'
            )
        );
    }

    /**
     * Add products field with append/remove hidden fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function addProductsField(FormBuilderInterface $builder)
    {
        $builder->add(
            'appendProducts',
            'oro_entity_identifier',
            array(
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Product',
                'required' => false,
                'mapped'   => false,
                'multiple' => true
            )
        );

        $builder->add(
            'removeProducts',
            'oro_entity_identifier',
            array(
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Product',
                'required' => false,
                'mapped'   => false,
                'multiple' => true
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
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Group'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_group';
    }
}
