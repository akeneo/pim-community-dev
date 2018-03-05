<?php

namespace Pim\Component\User\User;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * User repository interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Return users who are AT LEAST in one of the given $groupIds
     *
     * @param array $groupIds
     *
     * @return \Pim\Component\User\User\UserInterface[]
     */
    public function findByGroupIds(array $groupIds);
}
