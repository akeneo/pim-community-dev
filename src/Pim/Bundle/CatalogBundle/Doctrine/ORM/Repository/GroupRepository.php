<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

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
    public function countVariantGroupAxis(AttributeInterface $attribute)
    {
        $qb = $this->createQueryBuilder('g');

        return $qb
            ->select('count(g.id)')
            ->join('g.axisAttributes', 'attributes')
            ->where(
                $qb->expr()->in('attributes', ':attribute')
            )
            ->setParameter('attribute', $attribute)
            ->getQuery()
            ->getSingleScalarResult();
    }

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
    public function getAllGroupsExceptVariant()
    {
        $qb = $this->createQueryBuilder('grp');
        $qb->innerJoin('grp.type', 'type')
            ->where($qb->expr()->eq('type.variant', ':variant'))
            ->setParameter(':variant', false);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllVariantGroups()
    {
        return $this->getAllVariantGroupsQB()->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function createAssociationDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('g');

        $groupLabelExpr = '(CASE WHEN translation.label IS NULL THEN g.code ELSE translation.label END)';
        $typeLabelExpr = '(CASE WHEN typTrans.label IS NULL THEN typ.code ELSE typTrans.label END)';

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
            ->leftJoin('g.type', 'typ')
            ->leftJoin('typ.translations', 'typTrans', 'WITH', 'typTrans.locale = :dataLocale');

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

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(array $ids, $attributeCode)
    {
        if ($this->hasAttributeInAxisAttributes($ids, $attributeCode)) {
            return true;
        }

        return $this->hasAttributeInProductTemplate($ids, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantGroupByProductTemplate(ProductTemplateInterface $productTemplate)
    {
        $qb = $this->createQueryBuilder('g');

        $qb->innerJoin('g.productTemplate', 'pt')
            ->where($qb->expr()->eq('pt', ':productTemplate'))
            ->setParameter(':productTemplate', $productTemplate->getId());

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array  $ids
     * @param string $attributeCode
     *
     * @return bool
     */
    protected function hasAttributeInAxisAttributes(array $ids, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->leftJoin('g.axisAttributes', 'a')
            ->leftJoin('g.type', 't')
            ->where('g.id IN (:ids)')
            ->andWhere('t.variant = :variant')
            ->andWhere('a.code = :code')
            ->setMaxResults(1)
            ->setParameters([
                'variant' => true,
                'code'    => $attributeCode,
                'ids'     => $ids,
            ]);

        $result = $queryBuilder->getQuery()->getArrayResult();

        return count($result) > 0;
    }

    /**
     * @param array  $ids
     * @param string $attributeCode
     *
     * @return bool
     */
    protected function hasAttributeInProductTemplate(array $ids, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('pt.valuesData')
            ->leftJoin('g.type', 't')
            ->leftJoin('g.productTemplate', 'pt')
            ->where('g.id IN (:ids)')
            ->andWhere('t.variant = :variant')
            ->setParameters([
                'variant' => true,
                'ids'     => $ids,
            ]);

        $productTemplates = $queryBuilder->getQuery()->getArrayResult();

        foreach ($productTemplates as $productTemplate) {
            if (isset($productTemplate['valuesData'][$attributeCode])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'ProductGroup';
    }

    /**
     * Get ordered groups query builder
     *
     * @param GroupTypeInterface $type
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGroupsByTypeQB(GroupTypeInterface $type)
    {
        $alias = $this->getAlias();

        return $this->createQueryBuilder($alias)
            ->where($alias.'.type = :groupType')
            ->addOrderBy($alias.'.code', 'ASC')
            ->setParameter('groupType', $type);
    }

    /**
     * Get all variant groups query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllVariantGroupsQB()
    {
        $qb = $this->createQueryBuilder('g');

        return $qb->innerJoin('g.type', 'type')
            ->where($qb->expr()->eq('type.variant', ':variant'))
            ->setParameter(':variant', true);
    }
}
