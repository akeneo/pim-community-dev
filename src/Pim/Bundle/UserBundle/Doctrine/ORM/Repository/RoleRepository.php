<?php

namespace Pim\Bundle\UserBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Component\User\Model\RoleInterface;
use Pim\Component\User\Repository\RoleRepositoryInterface;

/**
 * Role repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleRepository extends EntityRepository implements RoleRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['role'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['role' => $code]);
    }

    /**
     * Create a QB to find all roles but the anonymous one
     *
     * @return QueryBuilder
     */
    public function getAllButAnonymousQB(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->where('r.role <> :anon')
            ->setParameter('anon', User::ROLE_ANONYMOUS);
    }

    /**
     * Get user query builder
     *
     * @param  RoleInterface $role
     *
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(RoleInterface $role): QueryBuilder
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('PimUserBundle:User', 'u')
            ->join('u.roles', 'role')
            ->where('role = :role')
            ->setParameter('role', $role);
    }
}
