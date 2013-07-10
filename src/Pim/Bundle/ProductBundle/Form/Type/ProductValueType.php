<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Pim\Bundle\ProductBundle\Form\View\ProductFormView;
use Pim\Bundle\ProductBundle\Entity\ProductValue;

/**
 * Product value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueType extends FlexibleValueType
{
    protected $productFormView;

    /**
     * {@inheritdoc}
     */
    public function __construct(FlexibleManager $flexibleManager, EventSubscriberInterface $subscriber, ProductFormView $productFormView)
    {
        parent::__construct($flexibleManager, $subscriber);

        $this->productFormView = $productFormView;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getData() instanceof ProductValue) {
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
