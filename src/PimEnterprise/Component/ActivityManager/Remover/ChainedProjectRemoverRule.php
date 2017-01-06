<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Remover;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Chained project remover rule
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ChainedProjectRemoverRule implements ProjectRemoverRuleInterface
{
    /** @var ProjectRemoverRuleInterface[] */
    protected $rules = [];

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToBeRemoved(ProjectInterface $project, $entity)
    {
        foreach ($this->rules as $rule) {
            if ($rule->hasToBeRemoved($project, $entity)) {
                return true;
            }
        }

        return false;
    }
}
