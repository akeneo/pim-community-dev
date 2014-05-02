<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;

/**
 * Subscriber to manage rights on attribute groups
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupRightsSubscriber implements EventSubscriberInterface
{
    /**
     * @var AttributeGroupAccessManager
     */
    protected $accessManager;

    /**
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(AttributeGroupAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT  => 'postSubmit'
        );
    }

    /**
     * Inject existing rights into the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSetData(FormEvent $event)
    {
        if (null === $event->getData()) {
            return;
        }

        $form = $event->getForm();
    }

    /**
     * Persist the rights defined in the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->isValid()) {
            $group = $event->getData();
            $view  = $form->get('rights')->get('view')->getData();
            $edit  = $form->get('rights')->get('edit')->getData();
            $this->accessManager->setAccess($group, $view, $edit);
        }
    }
}
