<?php

namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
     * @var bool $productEnabled
     */
    protected $productEnabled;

    /**
     * Set wether or not the product should be enabled
     *
     * @param bool $productEnabled
     *
     * @return TransformImportedProductDataSubscriber
     */
    public function setProductEnabled($productEnabled)
    {
        $this->productEnabled = $productEnabled;

        return $this;
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

        $dataToSubmit = array();
        $dataToSubmit = array_merge($this->getProductEnabledData(), $dataToSubmit);

        $event->setData($dataToSubmit);
    }

    /**
     * Return form data to set product enabling (empty array if we don't know)
     *
     * @return array
     */
    private function getProductEnabledData()
    {
        if (null !== $this->productEnabled) {
            return array('enabled' => $this->productEnabled);
        }

        return array();
    }
}
