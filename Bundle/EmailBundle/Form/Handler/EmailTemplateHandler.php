<?php

namespace Oro\Bundle\EmailBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateHandler
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
     * @var ObjectManager
     */
    protected $translator;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     * @param Translator $translator
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager, Translator $translator)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     * Process form
     *
     * @param  EmailTemplate  $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(EmailTemplate $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            // deny to modify system templates
            if ($entity->getIsSystem()) {
                $message = $this->translator->trans(
                    'oro.mail.validators.emailtemplate.attempt_save_system_template',
                    array(),
                    'validators'
                );
                $this->form->addError(new FormError($message));

                return false;
            }

            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->manager->persist($entity);
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
