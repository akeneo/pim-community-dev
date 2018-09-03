<?php

namespace Akeneo\UserManagement\Component\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupRepositoryInterface extends IdentifiableObjectRepositoryInterface
{
    /**
     * Get the default user group
     *
     * @return null|object
     */
    public function getDefaultUserGroup();
}
