<?php

namespace Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * User group repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository implements GroupRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['name' => $code]);
    }

    /**
     * Get the default user group
     *
     * @return null|object
     */
    public function getDefaultUserGroup()
    {
        return $this->findOneByIdentifier(User::GROUP_DEFAULT);
    }

    /**
     * Find all groups but the default one
     *
     * @return array
     */
    public function findAllButDefault()
    {
        return $this->getAllButDefaultQB()->getQuery()->getResult();
    }

    /**
     * Create a QB to find all groups but the default one
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllButDefaultQB()
    {
        return $this->createQueryBuilder('g')
            ->where('g.name <> :all')
            ->setParameter('all', User::GROUP_DEFAULT);
    }

    /**
     * Get user query builder
     *
     * @param  GroupInterface $group
     *
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(GroupInterface $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from(UserInterface::class, 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
