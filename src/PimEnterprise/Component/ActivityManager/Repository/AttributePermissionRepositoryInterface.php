<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;
use Pim\Component\User\Model\GroupInterface;


/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface AttributePermissionRepositoryInterface
{
    /**
     * Find user groups which have the edit right permission on the attribute GroupInterface selection.
     *
     * @param array $attributeGroupIdentifiers
     *
     * @return GroupInterface[]
     */
    public function findContributorsUserGroups(array $attributeGroupIdentifiers);
}
