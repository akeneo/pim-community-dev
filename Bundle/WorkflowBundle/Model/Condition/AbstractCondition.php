<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

abstract class AbstractCondition implements ConditionInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($context, Collection $errors = null)
    {
        $isAllowed = $this->isConditionAllowed($context);
        if (!$isAllowed) {
            $this->addError($errors);
        }

        return $isAllowed;
    }

    /**
     * @param Collection|null $errors
     */
    protected function addError(Collection $errors = null)
    {
        if ($errors && $this->message) {
            $errors->add($this->message);
        }
    }

    /**
     * This method should be overridden in descendant classes
     *
     * @param mixed $context
     * @return boolean
     */
    protected function isConditionAllowed($context)
    {
        return false;
    }
}
