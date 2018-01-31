<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractUserHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var UserManager
     */
    protected $manager;

    /**
     *
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param UserManager   $manager
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, UserManager $manager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  UserInterface $user
     * @return bool True on successfull processing, false otherwise
     */
    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    /**
     * Get Request
     *
     * @return null|Request
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * "Success" form handler
     *
     * @param UserInterface $user
     */
    abstract protected function onSuccess(UserInterface $user);
}
