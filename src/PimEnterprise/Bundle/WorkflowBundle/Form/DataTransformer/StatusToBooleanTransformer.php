<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\DataTransformer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class StatusToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return Proposition::READY === $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value ? Proposition::READY : Proposition::IN_PROGRESS;
    }
}
