<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\User;

/**
 * User group repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['name' => $code]);
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
     * Get the default user group
     *
     * @return null|Group
     */
    public function getDefaultUserGroup()
    {
        return $this->findOneByIdentifier(User::GROUP_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['name'];
    }

    /**
     * Get user query builder
     *
     * @param Group $group
     *
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Group $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('PimUserBundle:User', 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
