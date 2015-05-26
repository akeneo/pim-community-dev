<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;

/**
 * Repository for reference data entities
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepository extends EntityRepository implements
    ReferenceDataRepositoryInterface,
    IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        if (null !== $labelProperty = $this->getReferenceDataLabelProperty()) {
            $selectDql = sprintf(
                '%s.id as id, ' .
                'CASE WHEN %s.%s IS NULL OR %s.%s = \'\' THEN CONCAT(\'[\', %s.code, \']\') ELSE %s.%s END AS text',
                $this->getAlias(),
                $this->getAlias(),
                $labelProperty,
                $this->getAlias(),
                $labelProperty,
                $this->getAlias(),
                $this->getAlias(),
                $labelProperty
            );
        } else {
            $selectDql = sprintf(
                '%s.id as id, CONCAT(\'[\', %s.code, \']\') as text',
                $this->getAlias(),
                $this->getAlias()
            );
        }

        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->select($selectDql);

        if ($this->getClassMetadata()->hasField('sortOrder')) {
            $qb->orderBy(sprintf('%s.sortOrder', $this->getAlias()), 'DESC');
            $qb->addOrderBy(sprintf('%s.code', $this->getAlias()));
        } else {
            $qb->orderBy(sprintf('%s.code', $this->getAlias()));
        }

        if (null !== $search) {
            $searchDql = sprintf('%s.code LIKE :search', $this->getAlias());
            if (null !== $labelProperty) {
                $searchDql .= sprintf(' OR %s.%s LIKE :search', $this->getAlias(), $labelProperty);
            }
            $qb->andWhere($searchDql)->setParameter('search', "%$search%");
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
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

    /**
     * The list of label property of the reference data
     *
     * @return string|null
     */
    private function getReferenceDataLabelProperty()
    {
        $referenceDataClass = $this->getEntityName();

        return $referenceDataClass::getLabelProperty();
    }
}
