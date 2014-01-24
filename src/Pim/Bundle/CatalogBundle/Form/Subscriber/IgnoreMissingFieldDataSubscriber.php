<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Ignore missing product property
 * Default behaviour of symfony Form component is to set to null values of missing data
 * for a field that is defined in a form type.
 * So, in order to ignore it, it needs to be removed from the form.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IgnoreMissingFieldDataSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit',
        );
    }

    /**
     * Remove a form field if it wasn't submitted
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->isValid()) {
            return;
        }

        $data = $event->getData();
        foreach (array_keys($form->all()) as $name) {
            if (!array_key_exists($name, $data)) {
                $form->remove($name);
            }
        }
    }
}
