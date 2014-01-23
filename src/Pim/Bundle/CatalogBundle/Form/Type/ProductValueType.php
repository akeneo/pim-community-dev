<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueType extends AbstractType
{
    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @var string
     */
    protected $valueClass;

    /**
     * @var ProductFormView $productFormView
     */
    protected $productFormView;

    /**
     * @param string                   $valueClass      the product value class
     * @param EventSubscriberInterface $subscriber      the subscriber
     * @param ProductFormView          $productFormView the form view
     */
    public function __construct(
        $valueClass,
        EventSubscriberInterface $subscriber,
        ProductFormView $productFormView
    ) {
        $this->subscriber      = $subscriber;
        $this->valueClass      = $valueClass;
        $this->productFormView = $productFormView;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getData() instanceof ProductValueInterface) {
            $this->productFormView->addChildren($form->getData(), $view);
        }

        $view->vars['mode'] = isset($options['block_config']['mode']) ? $options['block_config']['mode'] : 'normal';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->valueClass,
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_value';
    }
}
