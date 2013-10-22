<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;

/**
 * Product form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends FlexibleType
{

    /**
     * {@inheritdoc}
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        $valueFormAlias,
        ProductFormView $productFormView,
        EventSubscriberInterface $transformer
    ) {
        parent::__construct($flexibleManager, $valueFormAlias);

        $this->productFormView = $productFormView;
        $this->transformer     = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['enable_state']) {
            $builder->add('enabled', 'checkbox');
        }

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $builder->add('enabled', 'hidden');
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'pim_catalog_localized_collection',
            array(
                'type'               => $this->valueFormAlias,
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
                'currentLocale'      => $options['currentLocale'],
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
                'currentLocale' => null,
                'enable_family' => true,
                'enable_state'  => true
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
