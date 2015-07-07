<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\Repository\GroupRepository as BaseGroupRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * User group repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\UserBundle\Doctrine\ORM\Repository in 1.4
 */
class GroupRepository extends BaseGroupRepository implements
    IdentifiableObjectRepositoryInterface,
    ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(array('name' => $code));
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
     * @return null|object
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
        return array('name');
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function getReferenceProperties()
    {
        return $this->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function findByReference($code)
    {
        return $this->findOneByIdentifier($code);
    }
}
