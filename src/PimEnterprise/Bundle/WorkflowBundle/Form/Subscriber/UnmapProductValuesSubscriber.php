<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\ResolvedFormTypeInterface;

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
            foreach ($valueField as $name => $field) {
                $this->unmapOne($field);
            }
        }
    }

    /**
     * Unmap one field children
     *
     * @param FormInterface $form
     */
    protected function unmapOne(FormInterface $form)
    {
        $config = $form->getConfig();
        if ($this->isACollectionType($config->getType())) {

            // Collection fields must be treated separatly as sub-fields can be created on the fly
            $options = $config->getOptions();
            $options['options']['mapped'] = false;
            $form
                ->getParent()
                ->add(
                    $form->getName(),
                    $config->getType()->getInnerType(),
                    $options
                );

        } elseif ($form->count() === 0) {

            // Unmap only leaf of the form tree
            $form
                ->getParent()
                ->add(
                    $form->getName(),
                    $config->getType()->getInnerType(),
                    array_merge($config->getOptions(), ['mapped' => false])
                );

        } else {

            foreach ($form as $field) {
                $this->unmapOne($field);
            }

        }
    }

    /**
     * Wether the form type is a collection type or extends it
     *
     * @param ResolvedFormTypeInterface $type
     *
     * return boolean
     */
    protected function isACollectionType(ResolvedFormTypeInterface $type)
    {
        if ($type->getInnerType() instanceof CollectionType) {
            return true;
        }

        if (null !== $parent = $type->getParent()) {
            return $this->isACollectionType($parent);
        }

        return false;
    }
}
