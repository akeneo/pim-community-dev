<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

class Andx extends AbstractComposite
{
    /**
     * Check if all conditions meets context.
     *
     * @param mixed $context
     * @param Collection|null $errors
     * @return boolean
     */
    public function isAllowed($context, Collection $errors = null)
    {
        if (!$this->conditions) {
            $this->addError($context, $errors);
            return false;
        }

        foreach ($this->conditions as $condition) {
            if (!$condition->isAllowed($context, $errors)) {
                $this->addError($context, $errors);
                return false;
            }
        }

        return true;
    }
}
