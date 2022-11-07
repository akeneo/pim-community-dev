<?php

namespace Akeneo\UserManagement\Component\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * User repository interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository, CountableRepositoryInterface
{
    /**
     * Return users who are AT LEAST in one of the given $groupIds
     *
     * @param array $groupIds
     *
     * @return UserInterface[]
     */
    public function findByGroupIds(array $groupIds);
}
