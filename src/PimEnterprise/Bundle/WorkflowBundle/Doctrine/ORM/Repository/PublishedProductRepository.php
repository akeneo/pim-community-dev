<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function setChannelRepository(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function setLocaleRepository($localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductQueryBuilderFactory(ProductQueryBuilderFactoryInterface $factory)
    {
        $this->queryBuilderFactory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct)
    {
        return $this->findOneBy(['originalProduct' => $originalProduct->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalProductId)
    {
        return $this->findOneBy(['originalProduct' => $originalProductId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByVersionId($versionId)
    {
        return $this->findOneBy(['version' => $versionId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProducts(array $originalProducts)
    {
        $originalIds = [];
        foreach ($originalProducts as $product) {
            $originalIds[] = $product->getId();
        }

        $qb = $this->createQueryBuilder('pp');
        $qb
            ->where($qb->expr()->in('pp.originalProduct', ':originalIds'))
            ->setParameter(':originalIds', $originalIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedVersionIdByOriginalProductId($originalId)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->select('IDENTITY(pp.version) AS version_id')
            ->where('pp.originalProduct = :originalId')
            ->setParameter('originalId', $originalId);

        try {
            $versionId = (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $versionId = null;
        }

        return $versionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping(array $originalIds = [])
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp.id AS published_id, IDENTITY(pp.originalProduct) AS original_id');
        if (!empty($originalIds)) {
            $qb->andWhere($qb->expr()->in('pp.originalProduct', $originalIds));
        }

        $ids = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $ids[intval($row['original_id'])] = intval($row['published_id']);
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForFamily(FamilyInterface $family)
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);

        return $productQb->execute()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForCategory(CategoryInterface $category)
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('categories', Operators::IN_CHILDREN_LIST, [$category->getCode()]);

        return $productQb->execute()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttribute(AttributeInterface $attribute)
    {
        if (!$attribute->isLocalizable()) {
            $count = $this->countPublishedProductsForNonLocalizableAttribute($attribute);
        } else {
            $count = $this->countPublishedProductsForLocalizableAttribute($attribute);
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForGroup(GroupInterface $group)
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('groups', Operators::IN_LIST, [$group->getCode()]);

        return $productQb->execute()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.associations', 'ppa')
            ->andWhere('ppa.associationType = :association_type')
            ->setParameter('association_type', $associationType);

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttributeOption(AttributeOptionInterface $option)
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter($option->getAttribute()->getCode(), Operators::IN_LIST, [$option->getCode()]);

        return $productQb->execute()->count();
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    protected function countPublishedProductsForNonLocalizableAttribute(AttributeInterface $attribute)
    {
        if ($attribute->isScopable()) {
            $count = 0;
            $channelCodes = $this->channelRepository->getChannelCodes();

            foreach ($channelCodes as $channelCode) {
                $productQb = $this->queryBuilderFactory->create();
                $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '', ['scope'  => $channelCode]);

                $count += $productQb->execute()->count();
            }
        } else {
            $productQb = $this->queryBuilderFactory->create();
            $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '');

            $count = $productQb->execute()->count();
        }

        return $count;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    protected function countPublishedProductsForLocalizableAttribute(AttributeInterface $attribute)
    {
        $count = 0;

        if ($attribute->isLocaleSpecific()) {
            $localeCodes = $attribute->getAvailableLocaleCodes();
        } else {
            $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        }

        if (!$attribute->isScopable()) {
            foreach ($localeCodes as $localeCode) {
                $productQb = $this->queryBuilderFactory->create();
                $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '', ['locale'  => $localeCode]);

                $count += $productQb->execute()->count();
            }
        } else {
            $channels = $this->channelRepository->findAll();

            foreach ($channels as $channel) {
                foreach ($channel->getLocaleCodes() as $localeCode) {
                    if (in_array($localeCode, $localeCodes)) {
                        $productQb = $this->queryBuilderFactory->create();
                        $productQb->addFilter(
                            $attribute->getCode(),
                            Operators::IS_NOT_EMPTY,
                            '',
                            [
                                'locale' => $localeCode,
                                'scope'  => $channel->getCode(),
                            ]
                        );

                        $count += $productQb->execute()->count();
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Return the result count from a query builder object
     *
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    protected function getCountFromQB(QueryBuilder $qb)
    {
        $rootAlias = current($qb->getRootAliases());
        $qb->select(sprintf("COUNT(%s.id)", $rootAlias));

        return $qb->getQuery()->getSingleScalarResult();
    }
}
