<?php

namespace PimEnterprise\Bundle\SecurityBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to remove product value when user has no right to at least see it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveProductValueSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'removeProductValues'
        );
    }

    /**
     * Remove the product values field
     *
     * @param FormEvent $event
     */
    public function removeProductValues(FormEvent $event)
    {
        $form       = $event->getForm();
        $formValues = $form->get('values');

        foreach ($formValues as $formValue) {
            $productValue = $formValue->getData();
            // TODO: exemple
            if ($productValue->getAttribute()->getCode() === 'sku') {
                $formValueName = $formValue->getName();
                $formValues->remove($formValueName);
            }
        }
    }
}
