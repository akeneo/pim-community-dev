<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to disable entity code modification after the entity has been created
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableCodeFieldSubscriber implements EventSubscriberInterface
{
    protected $fieldName;

    /**
     * Constructor
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData'
        );
    }

    /**
     * Disable the code field
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $entity = $event->getData();
        if (null === $entity || !$entity->getId()) {
            return;
        }

        $form  = $event->getForm();
        $field = $form->get($this->fieldName);

        $type    = $field->getConfig()->getType()->getName();
        $options = $field->getConfig()->getOptions();

        $options['disabled']  = true;
        $options['read_only'] = true;
        $options['auto_initialize'] = false;

        $form->add($this->fieldName, $type, $options);
    }
}
