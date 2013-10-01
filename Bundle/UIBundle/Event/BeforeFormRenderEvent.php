<?php

namespace Oro\Bundle\UIBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Twig_Environment;
use Symfony\Component\Form\FormView;

class BeforeFormRenderEvent extends Event
{
    /**
     * @var FormView
     */
    protected $form;

    /**
     * Array of form data collected in entity update template
     *
     * @var array
     */
    protected $formData;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnvironment;

    /**
     * @param FormView $form
     * @param array $formData
     * @param \Twig_Environment $twigEnvironment
     */
    public function __construct(FormView $form, array $formData, Twig_Environment $twigEnvironment)
    {
        $this->form                = $form;
        $this->formData            = $formData;
        $this->twigEnvironment     = $twigEnvironment;
    }

    /**
     * @return FormView
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return $this->formData;
    }

    public function setFormData(array $formData)
    {
        $this->formData = $formData;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwigEnvironment()
    {
        return $this->twigEnvironment;
    }
}
