<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Unmap product value fields so they are not changed during submission
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class UnmapProductValuesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'unmapAll',
        ];
    }

    /**
     * Unmap all the values field
     *
     * @param FormEvent $event
     */
    public function unmapAll(FormEvent $event)
    {
        $form = $event->getForm();
        if ('pim_product_edit' !== $form->getName()) {
            return;
        }

        foreach ($form->get('values') as $valueField) {
            $this->unmapOne($valueField);
        }
    }

    /**
     * Unmap one field children
     *
     * @param FormInterface $form
     */
    protected function unmapOne(FormInterface $form)
    {
        foreach ($form as $name => $field) {
            $config = $field->getConfig();
            $form->add(
                $name,
                $config->getType()->getInnerType(),
                array_merge($config->getOptions(), ['mapped' => false])
            );
        }
    }
}
