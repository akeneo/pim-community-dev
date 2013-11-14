<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class InvalidTransitionException extends WorkflowException
{
    const UNKNOWN_TRANSITION = 1;
    const NOT_START_TRANSITION = 2;
    const STEP_HAS_NO_ALLOWED_TRANSITION = 3;

    public static function unknownTransition($transitionName)
    {
        return new self(
            sprintf('Transition "%s" is not exist in workflow.', $transitionName),
            self::UNKNOWN_TRANSITION
        );
    }

    public static function notStartTransition($workflowName, $transitionName)
    {
        return new self(
            sprintf('Transition "%s" is not a start transition of workflow "%s".', $transitionName, $workflowName),
            self::NOT_START_TRANSITION
        );
    }

    public static function stepHasNoAllowedTransition($workflowName, $stepName, $transitionName)
    {
        return new self(
            sprintf(
                'Step "%s" of workflow "%s" doesn\'t have allowed transition "%s".',
                $stepName,
                $workflowName,
                $transitionName
            ),
            self::STEP_HAS_NO_ALLOWED_TRANSITION
        );
    }
}
