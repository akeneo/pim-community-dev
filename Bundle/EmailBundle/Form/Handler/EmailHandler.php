<?php

namespace Oro\Bundle\EmailBundle\Form\Handler;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EmailBundle\Form\Model\Email;

class EmailHandler
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
     * @param FormInterface $form
     * @param Request       $request
     */
    public function __construct(FormInterface $form, Request $request)
    {
        $this->form    = $form;
        $this->request = $request;
    }

    /**
     * Process form
     *
     * @param  Email $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(Email $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {

                return true;
            }
        }

        return false;
    }
}
