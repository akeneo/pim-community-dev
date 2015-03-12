<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;

/**
 * Repository for reference data entities
 *
 * TODO-CR: should not implement IdentifiableObjectRepositoryInterface: done only to be able to use reference data in
 *          the transformers
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepository extends EntityRepository implements
 ReferenceDataRepositoryInterface, IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->select(sprintf('%s.id as id, %s.code as text', $this->getAlias(), $this->getAlias()));

        if (null !== $search) {
            $qb->andWhere(sprintf('%s.code LIKE :search', $this->getAlias()))
                ->setParameter('search', "$search%");
        } else {
            $options['limit'] = ReferenceDataRepositoryInterface::LIMIT_IF_NO_SEARCH;
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] -1));
            }
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * TODO-RD: should be dropped when we'll no more rely on IdentifiableObjectRepositoryInterface
     *
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * TODO-RD: should be dropped when we'll no more rely on IdentifiableObjectRepositoryInterface
     *
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * Alias of the repository
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'rd';
    }
}
