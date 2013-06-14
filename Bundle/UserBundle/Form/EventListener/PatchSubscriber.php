<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Changes Form->bind() behavior so that it treats not set values as if they
 * were sent unchanged.
 *
 * Use when you don't want fields to be set to NULL when they are not displayed
 * on the page (or to implement PUT/PATCH requests).
 */
class PatchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }

    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $data = array_replace($this->unbind($form), $data ?: array());

        $event->setData($data);
    }

    /**
     * Returns the form's data like $form->submit() expects it
     *
     * @param FormInterface $form
     * @return array
     */
    protected function unbind($form)
    {
        if ($form->count() > 0) {
            $ary = array();

            foreach ($form->all() as $name => $child) {
                $ary[$name] = $this->unbind($child);
            }

            return $ary;
        } else {
            return $form->getViewData();
        }
    }
}
