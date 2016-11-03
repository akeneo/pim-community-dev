<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Repository;

use Oro\Bundle\UserBundle\Entity\Group;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface AttributePermissionRepositoryInterface
{
    /**
     * Find user groups which have the edit right permission on the attribute group selection.
     *
     * @param array $attributeGroupIdentifiers
     *
     * @return Group[]
     */
    public function findContributorsUserGroups(array $attributeGroupIdentifiers);
}
