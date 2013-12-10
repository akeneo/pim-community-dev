<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to disable field modification after the entity has been created
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var string The name of the field to disable
     */
    protected $fieldName;

    /**
     * @var string The name of the method used to determine whether the field should be disabled
     */
    protected $determinator;

    /**
     * Constructor
     *
     * @param string $fieldName
     * @param string $determinator
     */
    public function __construct($fieldName, $determinator = 'getId')
    {
        $this->fieldName    = $fieldName;
        $this->determinator = $determinator;
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
        $determinator = $this->determinator;
        if (null === $entity || !method_exists($entity, $determinator) || !$entity->$determinator()) {
            return;
        }

        $form   = $event->getForm();
        $config = $form->get($this->fieldName)->getConfig();

        $options = array(
            'disabled'  => true,
            'read_only' => true,
        );

        if ($help = $config->getOption('help')) {
            $options['help'] = $help;
        }
        if ($label = $config->getOption('label')) {
            $options['label'] = $label;
        }
        if ($select2 = $config->getOption('select2')) {
            $options['select2'] = $select2;
        }

        $form->add($this->fieldName, null, $options);
    }
}
