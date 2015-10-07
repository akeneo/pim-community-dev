<?php

namespace Oro\Bundle\NavigationBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NavigationBundle\Entity\PageState;

class PageStateHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     *
     * @param FormInterface         $form
     * @param Request               $request
     * @param ObjectManager         $manager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->form         = $form;
        $this->request      = $request;
        $this->manager      = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Process form
     *
     * @param  PageState $entity
     * @return bool      True on successfull processing, false otherwise
     */
    public function process(PageState $entity)
    {
        if ($this->tokenStorage->getToken() && is_object($user = $this->tokenStorage->getToken()->getUser())) {
            $entity->setUser($user);
        }

        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param PageState $entity
     */
    protected function onSuccess(PageState $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
