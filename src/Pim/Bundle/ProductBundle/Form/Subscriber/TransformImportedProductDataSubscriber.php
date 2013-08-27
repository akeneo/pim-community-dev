<?php

namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;

/**
 * Transform imported product data into a bindable data to the product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    protected $productEnabledConverter;
    protected $productValueConverter;
    protected $productFamilyConverter;

    public function __construct(
        ProductEnabledConverter $productEnabledConverter,
        ProductValueConverter $productValueConverter,
        ProductFamilyConverter $productFamilyConverter
    ) {
        $this->productEnabledConverter = $productEnabledConverter;
        $this->productValueConverter   = $productValueConverter;
        $this->productFamilyConverter  = $productFamilyConverter;
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

        $dataToSubmit = array(
            'enabled' => $this->productEnabledConverter->convert($data),
            'values' => $this->productValueConverter->convert($data),
            'family' => $this->productFamilyConverter->convert($data),
        );

        $event->setData($dataToSubmit);
    }
}
