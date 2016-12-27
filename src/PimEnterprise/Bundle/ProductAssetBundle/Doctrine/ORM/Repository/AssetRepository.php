<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Product asset repository
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AssetRepository extends EntityRepository implements AssetRepositoryInterface
{
    /** @var string */
    protected $productClass;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryFilterableRepositoryInterface */
    protected $categoryFilterableRepository;

    /**
     * @return string
     */
    public function getProductClass()
    {
        return $this->productClass;
    }

    /**
     * @param string $productClass
     */
    public function setProductClass($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     *
     */
    public function setCategoryRepository(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param CategoryFilterableRepositoryInterface $categoryFilterableRepository
     *
     */
    public function setAssetCategoryRepository(CategoryFilterableRepositoryInterface $categoryFilterableRepository)
    {
        $this->categoryFilterableRepository = $categoryFilterableRepository;
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
    public function findOneByIdentifier($assetCode)
    {
        return $this->findOneBy(['code' => $assetCode]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByCode($assetCode)
    {
        $qb = $this->createQueryBuilder('asset')
            ->where('asset.code = :assetCode')
            ->setParameter(':assetCode', $assetCode, \PDO::PARAM_STR);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->findBySearchQb($search, $options);

        $selectDql = sprintf(
            '%s.id as id, CONCAT(\'[\', %s.code, \']\') as text',
            $this->getAlias(),
            $this->getAlias()
        );
        $qb->select($selectDql);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Find entities with search and search options
     *
     * @param string|null  $search
     * @param array $options
     *
     * @return array
     */
    public function findEntitiesBySearch($search = null, array $options = [])
    {
        $qb = $this->findBySearchQb($search, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * Configures the search query
     *
     * @param null|string $search
     * @param array       $options
     *
     * @return QueryBuilder
     */
    protected function findBySearchQb($search = null, array $options = [])
    {
        $qb = $this->createQueryBuilder($this->getAlias());

        if ($this->getClassMetadata()->hasField('sortOrder')) {
            $qb->orderBy(sprintf('%s.sortOrder', $this->getAlias()), 'DESC');
            $qb->addOrderBy(sprintf('%s.code', $this->getAlias()));
        } else {
            $qb->orderBy(sprintf('%s.code', $this->getAlias()));
        }

        if (null !== $search) {
            $searchDql = sprintf('%s.code LIKE :search', $this->getAlias());
            $qb->andWhere($searchDql)->setParameter('search', "%$search%");
        }

        if (isset($options['categories'])) {
            $qb
                ->leftJoin(sprintf('%s.categories', $this->getAlias()), 'c')
                ->andWhere('c.id IN (:categories) OR c.id IS NULL')
                ->setParameter('categories', $options['categories']);
        }

        if (isset($options['identifiers']) && is_array($options['identifiers']) && !empty($options['identifiers'])) {
            $qb->andWhere(sprintf('%s.code in (:codes)', $this->getAlias()));
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        $qb->groupBy(sprintf('%s.id', $this->getAlias()));

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findSimilarCodes($code)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(sprintf('%s.code', $this->getAlias()))
            ->from($this->_entityName, $this->getAlias(), sprintf('%s.code', $this->getAlias()))
            ->andWhere(sprintf('%s.code LIKE :pattern', $this->getAlias()))
            ->orWhere(sprintf('%s.code = :code', $this->getAlias()))
            ->setParameters([
                ':pattern' => sprintf("%s_%s", $code, '%'),
                ':code'    => $code
            ]);

        return array_keys($qb->getQuery()->getArrayResult());
    }

    /**
     * {@inheritdoc}
     */
    public function createAssetDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->addGroupBy($this->getAlias().'.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function applyTagFilter(QueryBuilder $qb, $field, $operator, $value)
    {
        $qb->leftJoin(sprintf('%s.tags', $this->getAlias()), 'tags');

        switch ($operator) {
            case Operators::IN_LIST:
                $this->applyFilterInList($qb, $field, $value);
                break;
            case Operators::IS_EMPTY:
                $this->applyFilterEmpty($qb, $field);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifiers(array $identifiers = [])
    {
        $qb = $this->createQueryBuilder($this->getAlias())
            ->where($this->getAlias() . '.code IN (:identifiers)')
            ->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findProducts(AssetInterface $asset, $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from($this->getProductClass(), 'p')
            ->join('p.values', 'v')
            ->join('v.assets', 'a')
            ->where('a.id = :assetId')
            ->setParameter(':assetId', $asset->getId(), \PDO::PARAM_INT);

        return $qb->getQuery()->getResult($hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function countCompleteAssets(array $assetIds, $localeId, $channelId)
    {
        $selectSql = 'SELECT a.id,
            IF (r.locale_id IS NOT NULL, r.locale_id, cl.locale_id) AS locale_id,
            v.channel_id

            FROM pimee_product_asset_asset a
            JOIN pimee_product_asset_reference r ON r.asset_id = a.id
            JOIN pimee_product_asset_variation v ON v.reference_id = r.id
            LEFT JOIN pim_catalog_channel_locale AS cl ON v.channel_id = cl.channel_id AND r.locale_id IS NULL

            WHERE a.id IN (?)
            AND (r.locale_id = ? OR cl.locale_id = ?)
            AND v.channel_id = ?

            GROUP BY a.id, locale_id, channel_id

            HAVING COUNT(v.file_info_id) > 0';

        $dbalConnection = $this->_em->getConnection();

        $stmt = $dbalConnection->executeQuery(
            $selectSql,
            [
                $assetIds,
                $localeId,
                $localeId,
                $channelId
            ],
            [
                Connection::PARAM_INT_ARRAY,
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
            ]
        );

        return $stmt->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function findExpiringAssets(\DateTime $now, $delay = 5)
    {
        $qb = $this->_em->createQueryBuilder();

        $endOfUse1 = $now->add(new \DateInterval(sprintf("P%sD", $delay)))->setTime(0, 0, 0)->format('Y-m-d G:i:s');
        $endOfUse2 = $now->setTime(23, 59, 59)->format('Y-m-d G:i:s');

        $qb->select('asset')
            ->from($this->_entityName, $this->getAlias())
            ->where(':endOfUse1 < asset.endOfUseAt')
            ->andWhere('asset.endOfUseAt < :endOfUse2')
            ->setParameter(':endOfUse1', $endOfUse1)
            ->setParameter(':endOfUse2', $endOfUse2);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $assetIds)
    {
        if (empty($assetIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->in('a.id', ':asset_ids'));
        $qb->setParameter(':asset_ids', $assetIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function applyCategoriesFilter(QueryBuilder $qb, $operator, array $categoryCodes)
    {
        $categoryIds = $this->categoryRepository->getCategoryIdsByCodes($categoryCodes);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->categoryFilterableRepository->applyFilterByCategoryIds($qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_LIST:
                $this->categoryFilterableRepository->applyFilterByCategoryIds($qb, $categoryIds, false);
                break;
            case Operators::IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->categoryFilterableRepository->applyFilterByCategoryIds($qb, $categoryIds, true);
                break;
            case Operators::NOT_IN_CHILDREN_LIST:
                $categoryIds = $this->getAllChildrenIds($categoryIds);
                $this->categoryFilterableRepository->applyFilterByCategoryIds($qb, $categoryIds, false);
                break;
            case Operators::UNCLASSIFIED:
                $this->categoryFilterableRepository->applyFilterByUnclassified($qb);
                break;
            case Operators::IN_LIST_OR_UNCLASSIFIED:
                $this->categoryFilterableRepository->applyFilterByCategoryIdsOrUnclassified($qb, $categoryIds);
                break;
        }
    }

    /**
     * Apply an in list filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterInList(QueryBuilder $qb, $field, $value)
    {
        if (!empty($value)) {
            $qb->andWhere($qb->expr()->in($field, $value));
        }
    }

    /**
     * Apply a is_empty filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     */
    protected function applyFilterEmpty(QueryBuilder $qb, $field)
    {
        $qb->andWhere($qb->expr()->isNull($field));
    }

    /**
     * Alias of the repository
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'asset';
    }

    /**
     * Get children category ids
     *
     * @param integer[] $categoryIds
     *
     * @return integer[]
     */
    protected function getAllChildrenIds(array $categoryIds)
    {
        $allChildrenIds = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->find($categoryId);
            $childrenIds = $this->categoryRepository->getAllChildrenIds($category);
            $childrenIds[] = $category->getId();
            $allChildrenIds = array_merge($allChildrenIds, $childrenIds);
        }

        return $allChildrenIds;
    }
}
