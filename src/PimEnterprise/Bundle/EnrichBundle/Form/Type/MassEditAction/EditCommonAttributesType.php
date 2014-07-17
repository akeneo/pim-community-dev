<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\EditCommonAttributesType as BaseEditCommonAttributesType;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber\CollectProductValuesSubscriber;

/**
 * Form type of the EditCommonAttributes operation
 * configured with a data collector subscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EditCommonAttributesType extends BaseEditCommonAttributesType
{
    /** @var CollectProductValuesSubscriber */
    protected $subscriber;

    /**
     * @param ProductFormViewInterface       $productFormView
     * @param LocaleHelper                   $localeHelper
     * @param string                         $attributeClass
     * @param CollectProductValuesSubscriber $subscriber
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        LocaleHelper $localeHelper,
        $attributeClass,
        CollectProductValuesSubscriber $subscriber
    ) {
        parent::__construct($productFormView, $localeHelper, $attributeClass);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }
}
