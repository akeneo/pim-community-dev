<?php

namespace Oro\Bundle\FormBundle\JsValidation\Event;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Form\FormView;

abstract class AbstractEvent extends Event
{
    protected $formView;

    /**
     * @param FormView $formView
     */
    public function __construct(FormView $formView)
    {
        $this->formView = $formView;
    }

    /**
     * @return FormView
     */
    public function getFormView()
    {
        return $this->formView;
    }
}

