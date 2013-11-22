<?php

namespace Oro\Bundle\FormBundle\JsValidation\Event;

use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraint;

use Doctrine\Common\Collections\Collection;

class GetConstraintsEvent extends AbstractEvent
{
    /**
     * @var Collection
     */
    protected $constraints;

    public function __construct(FormView $formView, Collection $constraints)
    {
        parent::__construct($formView);
        $this->constraints = $constraints;
    }

    /**
     * @param Constraint $constraint
     */
    public function addConstraint(Constraint $constraint)
    {
        $this->constraints->add($constraint);
    }

    /**
     * @return Collection
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
}
