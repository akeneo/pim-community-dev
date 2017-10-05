<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PimEnterprise\Bundle\EnrichBundle\Form\Type\LocalePermissionsType;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to manage permissions on locales
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 *
 * @deprecated Will be removed in 2.1. Should be replaced by a subscriber not relying on form events
 *             (like PimEnterprise\Bundle\EnrichBundle\EventSubscriber\SavePermissionsSubscriber).
 *             Can be done with TIP-738.
 */
class LocalePermissionsSubscriber implements EventSubscriberInterface
{
    /** @var LocaleAccessManager */
    protected $accessManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param LocaleAccessManager $accessManager
     * @param SecurityFacade      $securityFacade
     */
    public function __construct(LocaleAccessManager $accessManager, SecurityFacade $securityFacade)
    {
        $this->accessManager = $accessManager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        ];
    }

    /**
     * Add the permissions subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $event->getForm()->add('permissions', LocalePermissionsType::class);
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm()->get('permissions');
        $form->get('view')->setData($this->accessManager->getViewUserGroups($event->getData()));
        $form->get('edit')->setData($this->accessManager->getEditUserGroups($event->getData()));
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            $viewUserGroups = $form->get('permissions')->get('view')->getData();
            $editUserGroups = $form->get('permissions')->get('edit')->getData();
            $this->accessManager->setAccess($event->getData(), $viewUserGroups, $editUserGroups);
        }
    }

    /**
     * Indicates whether the permissions should be added to the form
     *
     * @param FormEvent $event
     *
     * @return bool
     */
    protected function isApplicable(FormEvent $event)
    {
        return null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_locale_edit_permissions');
    }
}
