<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository as BaseRoleRepository;
use Pim\Bundle\UserBundle\Entity\User;

/**
 * Role repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\UserBundle\Doctrine\ORM\Repository in 1.4
 */
class RoleRepository extends BaseRoleRepository implements
    IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(array('label' => $code));
    }

    /**
     * Create a QB to find all roles but the anonymous one
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllButAnonymousQB()
    {
        return $this->createQueryBuilder('r')
            ->where('r.role <> :anon')
            ->setParameter('anon', User::ROLE_ANONYMOUS);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return array('label');
    }
}
