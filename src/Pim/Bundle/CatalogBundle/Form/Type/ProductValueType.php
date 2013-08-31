<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueType extends FlexibleValueType
{
    protected $productFormView;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        EventSubscriberInterface $subscriber,
        ProductFormView $productFormView
    ) {
        parent::__construct($flexibleManager, $subscriber);

        $this->productFormView = $productFormView;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getData() instanceof ProductValueInterface) {
            $this->productFormView->addChildren($form->getData(), $view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_value';
    }
}
