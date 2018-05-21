<?php

namespace Akeneo\UserManagement\Bundle\Form\Subscriber;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Changes Form->submit() behavior so that it treats not set values as if they
 * were sent unchanged.
 *
 * Use when you don't want fields to be set to NULL when they are not displayed
 * on the page (or to implement PUT/PATCH requests).
 */
class PatchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SUBMIT => 'preBind'];
    }

    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $data = array_replace($this->unbind($form), $data ?: []);

        $event->setData($data);
    }

    /**
     * Returns the form's data like $form->submit() expects it
     *
     * @param FormInterface $form
     * @return array
     */
    protected function unbind(FormInterface $form)
    {
        if ($form->count() > 0) {
            $ary = [];

            foreach ($form->all() as $name => $child) {
                $value = $this->unbind($child);
                if (null !== $value // if not null
                    || (
                        (is_array($value) || $value instanceof Collection)
                        && count($value) > 0 // if not empty array or collection
                    )
                ) {
                    $ary[$name] = $value;
                }
            }

            return $ary;
        } else {
            $data = $form->getViewData();

            return $data instanceof Collection ? $data->toArray() : $data;
        }
    }
}
