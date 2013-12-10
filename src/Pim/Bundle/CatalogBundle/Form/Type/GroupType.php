<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BindGroupProductsSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductAttributeRepository;

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
        $builder
            ->add('code')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        $this->addTypeField($builder);

        $this->addLabelField($builder);

        $this->addAttributesField($builder);

        $this->addProductsField($builder);
    }

    /**
     * Add type field
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addTypeField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'type',
                'entity',
                array(
                    'class' => 'PimCatalogBundle:GroupType',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository
                            ->buildAll()
                            ->andWhere('group_type.code != :variant')
                            ->setParameter('variant', 'VARIANT');
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'select2'  => true
                )
            )
            ->addEventSubscriber(new DisableFieldSubscriber('type', 'getType'));
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
     * Add attributes field
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addAttributesField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'attributes',
                'entity',
                array(
                    'label'    => 'Axis',
                    'required' => true,
                    'multiple' => true,
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
                    'query_builder' => function (ProductAttributeRepository $repository) {
                        return $repository->findAllAxisQB();
                    },
                    'help'     => 'pim_catalog.group.axis.help',
                    'select2'  => true
                )
            )
            ->addEventSubscriber(new DisableFieldSubscriber('attributes'));
    }

    /**
     * Add products field with append/remove hidden fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function addProductsField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'appendProducts',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Model\Product',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                )
            )
            ->add(
                'removeProducts',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Model\Product',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                )
            )
            ->addEventSubscriber(new BindGroupProductsSubscriber());
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
