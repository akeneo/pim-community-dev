<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

class Orx extends AbstractComposite
{
    /**
     * Check at lest one condition meets context.
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
            if ($condition->isAllowed($context, $errors)) {
                return true;
            }
        }

        $this->addError($context, $errors);

        return false;
    }
}
