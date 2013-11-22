<?php

namespace Oro\Bundle\FormBundle\JsValidation\EventListener;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use Oro\Bundle\FormBundle\JsValidation\Event\GetConstraintsEvent;

class RequiredConstraintListener
{
    /**
     * @param GetConstraintsEvent $event
     */
    public function onGetConstraints(GetConstraintsEvent $event)
    {
        $view = $event->getFormView();
        if (isset($view->vars['compound']) && $view->vars['compound']) {
            return;
        }
        if (!isset($view->vars['required']) || !$view->vars['required']) {
            return;
        }
        if (!$this->hasRequiredConstraint($event->getConstraints())) {
            $event->addConstraint($this->getNotBlankConstraint());
        }
    }

    /**
     * @param Constraint[] $constraints
     * @return bool
     */
    protected function hasRequiredConstraint($constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotBlank || $constraint instanceof NotNull) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return NotBlank
     */
    protected function getNotBlankConstraint()
    {
        return new NotBlank();
    }
}
