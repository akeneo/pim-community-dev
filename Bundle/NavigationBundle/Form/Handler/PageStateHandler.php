<?php

namespace Oro\Bundle\NavigationBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     *
     * @param FormInterface            $form
     * @param Request                  $request
     * @param ObjectManager            $manager
     * @param SecurityContextInterface $security
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        SecurityContextInterface $security
    ) {
        $this->form     = $form;
        $this->request  = $request;
        $this->manager  = $manager;
        $this->security = $security;
    }

    /**
     * Process form
     *
     * @param  PageState $entity
     * @return bool      True on successfull processing, false otherwise
     */
    public function process(PageState $entity)
    {
        if ($this->security->getToken() && is_object($user = $this->security->getToken()->getUser())) {
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
