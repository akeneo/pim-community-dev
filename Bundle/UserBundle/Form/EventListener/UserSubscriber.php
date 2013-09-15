<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Entity\User;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory;

    /**
     * @param FormFactoryInterface     $factory    Factory to add new form children
     * @param AclManager               $aclManager ACL manager
     * @param SecurityContextInterface $security   Security context
     */
    public function __construct(
        FormFactoryInterface $factory,
        SecurityContextInterface $security,
        ObjectIdentityFactory $objectIdentityFactory
    ) {
        $this->factory    = $factory;
        //$this->aclManager = $aclManager;
        $this->security   = $security;
        $this->objectIdentityFactory = $objectIdentityFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $submittedData = $event->getData();

        if (isset($submittedData['emails'])) {
            foreach ($submittedData['emails'] as $id => $email) {
                if (!$email['email']) {
                    unset($submittedData['emails'][$id]);
                }

            }
        }

        if ($submittedData['id']) {
            $permission = 'EDIT';
        } else {
            $permission = 'CREATE';
        }

        if (!$this->security->isGranted(
            $permission,
            $this->objectIdentityFactory->get('Entity:OroUserBundle:Role')
        )) {
            unset($submittedData['rolesCollection']);
        }

        if (!$this->security->isGranted(
            $permission,
            $this->objectIdentityFactory->get('Entity:OroUserBundle:Group')
        )) {
            unset($submittedData['groups']);
        }

        $event->setData($submittedData);
    }

    public function preSetData(FormEvent $event)
    {
        /* @var $entity User */
        $entity = $event->getData();
        $form   = $event->getForm();

        if (is_null($entity)) {
            return;
        }

        if ($entity->getId()) {
            $form->remove('plainPassword');
            $permission = 'EDIT';
        } else {
            $permission = 'CREATE';
        }

        if (!$this->security->isGranted(
            $permission,
            $this->objectIdentityFactory->get('Entity:OroUserBundle:Role')
        )) {
            $form->remove('rolesCollection');
        }

        if (!$this->security->isGranted(
            $permission,
            $this->objectIdentityFactory->get('Entity:OroUserBundle:Group')
        )) {
            $form->remove('groups');
        }

        /*if (!$this->aclManager->isResourceGranted('oro_user_role')) {
            $form->remove('rolesCollection');
        }

        if (!$this->aclManager->isResourceGranted('oro_user_group')) {
            $form->remove('groups');
        }*/

        // do not allow user to disable his own account
        $form->add(
            $this->factory->createNamed(
                'enabled',
                'choice',
                $entity->getId() ? $entity->isEnabled() : '',
                array(
                    'label'           => 'Status',
                    'required'        => true,
                    'disabled'        => $this->isCurrentUser($entity),
                    'choices'         => array('Inactive', 'Active'),
                    'empty_value'     => 'Please select',
                    'empty_data'      => '',
                    'auto_initialize' => false
                )
            )
        );
    }

    /**
     * Returns true if passed user is currently authenticated
     *
     * @param  User $user
     * @return bool
     */
    protected function isCurrentUser(User $user)
    {
        $token = $this->security->getToken();
        $currentUser = $token ? $token->getUser() : null;
        if ($user->getId() && is_object($currentUser)) {
            return $currentUser->getId() == $user->getId();
        }

        return false;
    }
}
