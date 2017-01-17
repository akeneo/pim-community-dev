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

/**
 * Removes impacted projects in terms of an entity which has been removed.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface ProjectRemoverInterface
{
    /**
     * Removes projects that have to be removed in terms of the given entity.
     *
     * @param mixed  $entity
     * @param string $action
     */
    public function removeProjectsImpactedBy($entity, $action = null);

    /**
     * Is the project remover supported for the given entity and action ?
     *
     * @param mixed  $entity
     * @param string $action
     *
     * @return bool
     */
    public function isSupported($entity, $action = null);
}
