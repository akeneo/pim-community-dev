<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

interface ConditionInterface
{
    /**
     * Set condition error message.
     *
     * @param string $message
     * @return ConditionInterface
     */
    public function setMessage($message);

    /**
     * Check if context meets condition requirements, optionally add error to collection
     *
     * @param mixed $context
     * @param Collection|null $errors
     * @return boolean
     */
    public function isAllowed($context, Collection $errors = null);

    /**
     * Initialize condition based on passed options.
     *
     * @param array $options
     * @return ConditionInterface
     * @throws ConditionException
     */
    public function initialize(array $options);
}
