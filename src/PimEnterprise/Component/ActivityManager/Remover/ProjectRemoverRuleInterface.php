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

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Guess from an entity which has been removed, if the given project is impacted and has to be removed.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface ProjectRemoverRuleInterface
{
    /**
     * Guess if project has to be removed in terms of the given entity.
     *
     * @param ProjectInterface $project
     * @param mixed            $entity
     *
     * @return bool
     */
    public function hasToBeRemoved(ProjectInterface $project, $entity);
}
