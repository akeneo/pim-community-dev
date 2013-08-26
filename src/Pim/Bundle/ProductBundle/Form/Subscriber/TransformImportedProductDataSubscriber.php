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
    protected $enabled;

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form instanceof \Pim\Bundle\ProductBundle\Form\Type\ProductType) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The %s subscriber is only compatible with the Pim\Bundle\ProductBundle\Form\Type\ProductType form, %s given',
                    get_class($this),
                    get_class($form)
                )
            );
        }
    }
}
