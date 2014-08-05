<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\Repository\GroupRepository as BaseGroupRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * User group repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends BaseGroupRepository implements ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(array('name' => $code));
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
     * @return null|object
     */
    public function getDefaultUserGroup()
    {
        return $this->findByReference(User::GROUP_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('name');
    }
}
