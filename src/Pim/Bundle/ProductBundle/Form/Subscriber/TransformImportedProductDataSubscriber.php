<?php

namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

/**
 * Transform imported product data into a bindable data to the product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProductEnabledConverter $productEnabledConverter
     */
    protected $productEnabledConverter;

    /**
     * @var ProductValueConverter $productValueConverter
     */
    protected $productValueConverter;

    /**
     * @var ProductFamilyConverter $productFamilyConverter
     */
    protected $productFamilyConverter;

    /**
     * @var ProductCategoriesConverter $productCategoriesConverter
     */
    protected $productCategoriesConverter;

    public function __construct(
        ProductEnabledConverter $productEnabledConverter,
        ProductValueConverter $productValueConverter,
        ProductFamilyConverter $productFamilyConverter,
        ProductCategoriesConverter $productCategoriesConverter
    ) {
        $this->productEnabledConverter    = $productEnabledConverter;
        $this->productValueConverter      = $productValueConverter;
        $this->productFamilyConverter     = $productFamilyConverter;
        $this->productCategoriesConverter = $productCategoriesConverter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * Transform the imported product data to allow binding them to the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $dataToSubmit = array_merge(
            $this->productEnabledConverter->convert($data),
            $this->productValueConverter->convert($data),
            $this->productFamilyConverter->convert($data),
            $this->productCategoriesConverter->convert($data)
        );

        $event->setData($dataToSubmit);
    }
}
