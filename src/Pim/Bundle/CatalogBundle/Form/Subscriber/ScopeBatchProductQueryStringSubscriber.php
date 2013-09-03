<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Allows to send "?products[]" instead of "?batch_products[products][]"
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeBatchProductQueryStringSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Higher priority than the Symfony\Component\Form\Extension\HttpFoundation\EventListener\BindRequestListener
        return array(FormEvents::PRE_SUBMIT => array('preSubmit', 129));
    }

    public function preSubmit(FormEvent $event)
    {

        $data = $event->getData();
        $form = $event->getForm();
        $name = $form->getConfig()->getName();

        $data->query = new ParameterBag(
            array(
                $name => array(
                    'products' => $data->query->get('products')
                )
            )
        );
    }
}
