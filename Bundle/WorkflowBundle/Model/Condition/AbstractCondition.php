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
    protected function getMessage()
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
            $this->addError($context, $errors);
        }

        return $isAllowed;
    }

    /**
     * @param mixed $context
     * @param Collection|null $errors
     */
    protected function addError($context, Collection $errors = null)
    {
        if ($errors && $this->getMessage()) {
            $messageParameters = $this->getMessageParameters($context);
            $errors->add(array('message' => $this->getMessage(), 'parameters' => $messageParameters));
        }
    }

    /**
     * @param mixed $context
     * @return array
     */
    protected function getMessageParameters($context)
    {
        return array();
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
