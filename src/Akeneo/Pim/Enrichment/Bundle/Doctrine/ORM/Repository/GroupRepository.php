<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Group repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository implements GroupRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = [])
    {
        $identifier = isset($options['type']) && 'code' === $options['type'] ? 'code' : 'id';

        $selectDQL = sprintf(
            'o.%s as id, COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', o.code, \']\')) as text',
            $identifier
        );

        $qb = $this->createQueryBuilder('o')
            ->select($selectDQL)
            ->leftJoin('o.translations', 't', 'WITH', 't.locale=:locale')
            ->addOrderBy('text', 'ASC')
            ->setParameter('locale', $dataLocale);

        if ($search) {
            $qb->andWhere('t.label like :search OR o.code LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if (isset($options['ids'])) {
            $qb
                ->andWhere(
                    $qb->expr()->in(sprintf('o.%s', $identifier), ':ids')
                )
                ->setParameter('ids', $options['ids']);
        }

        if (isset($options['limit']) && isset($options['page'])) {
            $qb->setFirstResult($options['limit'] * ($options['page'] - 1))
                ->setMaxResults($options['limit']);
        }

        $results = $qb->getQuery()->getArrayResult();

        return [
            'results' => $results
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createAssociationDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('g');

        $groupLabelExpr = 'COALESCE(translation.label, g.code)';
        $typeLabelExpr = 'COALESCE(typeTrans.label, type.code)';

        $isCheckecExpr =
            'CASE WHEN (g.id IN (:associatedIds) OR g.id IN (:data_in)) AND g.id NOT IN (:data_not_in) ' .
            'THEN true ELSE false END';

        $isAssociatedExpr = 'CASE WHEN g.id IN (:associatedIds) THEN true ELSE false END';

        $qb
            ->addSelect(sprintf('%s AS groupLabel', $groupLabelExpr))
            ->addSelect(sprintf('%s AS typeLabel', $typeLabelExpr))
            ->addSelect('translation.label')
            ->addSelect($isCheckecExpr.' AS is_checked')
            ->addSelect($isAssociatedExpr.' AS is_associated');

        $qb
            ->leftJoin('g.translations', 'translation', 'WITH', 'translation.locale = :dataLocale')
            ->leftJoin('g.type', 'type')
            ->leftJoin('type.translations', 'typeTrans', 'WITH', 'typeTrans.locale = :dataLocale');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }
}
