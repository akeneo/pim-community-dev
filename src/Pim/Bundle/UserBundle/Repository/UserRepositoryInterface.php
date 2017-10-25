<?php

namespace Pim\Bundle\UserBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\UserBundle\Entity\UserInterface;

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
     * Return the number of existing users
     *
     * @return int
     */
    public function countAll();

    /**
     * Return the number of users by criteria
     *
     * @return int
     */
    public function countBy(array $criteria);

    /**
     * Return users who are AT LEAST in one of the given $groupIds
     *
     * @param array $groupIds
     *
     * @return UserInterface[]
     */
    public function findByGroupIds(array $groupIds);
}
