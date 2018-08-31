<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;

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
                '%s.%s as id, ' .
                'CASE WHEN %s.%s IS NULL OR %s.%s = \'\' THEN CONCAT(\'[\', %s.code, \']\') ELSE %s.%s END AS text',
                $this->getAlias(),
                isset($options['type']) && 'code' === $options['type'] ? 'code' : 'id',
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
                '%s.%s as id, CONCAT(\'[\', %s.code, \']\') as text',
                $this->getAlias(),
                isset($options['type']) && 'code' === $options['type'] ? 'code' : 'id',
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
     * {@inheritdoc}
     */
    public function findCodesByIdentifiers(array $referenceDataCodes)
    {
        return $this->createQueryBuilder($this->getAlias())
            ->select($this->getAlias() . '.code')
            ->andWhere($this->getAlias() . '.code IN (:reference_data_codes)')
            ->setParameter('reference_data_codes', $referenceDataCodes)
            ->getQuery()
            ->getResult();
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
