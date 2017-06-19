<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CompletenessRepositoryInterface;

/**
 * Completeness Repository for ODM
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRepository implements CompletenessRepositoryInterface
{
    /** @var DocumentManager */
    protected $documentManager;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var string */
    protected $productClass;

    /**
     * @param DocumentManager             $documentManager
     * @param ChannelRepositoryInterface  $channelRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param string                      $productClass
     */
    public function __construct(
        DocumentManager $documentManager,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        $productClass
    ) {
        $this->documentManager = $documentManager;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountPerChannels($localeCode)
    {
        $channels = $this->channelRepository->findAll();
        $productRepo = $this->documentManager->getRepository($this->productClass);

        $productsCount = [];
        foreach ($channels as $channel) {
            $category = $channel->getCategory();
            $categoryIds = $this->categoryRepository->getAllChildrenIds($category, true);

            $total = $productRepo->createQueryBuilder()
                ->hydrate(false)
                ->field('categoryIds')->in($categoryIds)
                ->field('enabled')->equals(true)
                ->select('_id')
                ->getQuery()
                ->execute()
                ->count();

            $productsCount[] = [
                'label' => $channel->getLabel(),
                'total' => $total
            ];
        }

        return $productsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompleteProductsCountPerChannels($localeCode)
    {
        $channels = $this->channelRepository->findAll();
        $productRepo = $this->documentManager->getRepository($this->productClass);

        $productsCount = [];
        foreach ($channels as $channel) {
            $category = $channel->getCategory();
            $categoryIds = $this->categoryRepository->getAllChildrenIds($category, true);

            foreach ($channel->getLocales() as $locale) {
                $data = [];
                $compSuffix = $channel->getCode().'-'.$locale->getCode();

                $localeCount = $productRepo->createQueryBuilder()
                    ->hydrate(false)
                    ->field('categoryIds')->in($categoryIds)
                    ->field('enabled')->equals(true)
                    ->field('normalizedData.completenesses.'.$compSuffix)
                    ->equals(100)
                    ->select('_id')
                    ->getQuery()
                    ->execute()
                    ->count();

                $data['locale'] = $locale->getCode();
                $data['label'] = $channel->getLabel();
                $data['total'] = $localeCount;

                $productsCount[] = $data;
            }
        }

        return $productsCount;
    }

    /**
     * Return categories ids provided by the categoryQb
     *
     * @param OrmQueryBuilder $categoryQb
     *
     * @return array
     */
    protected function getCategoryIds(OrmQueryBuilder $categoryQb)
    {
        $categoryIds = [];
        $categoryAlias = current($categoryQb->getRootAliases());
        $categories = $categoryQb->select('PARTIAL '.$categoryAlias.'.{id}')->getQuery()->getArrayResult();

        foreach ($categories as $category) {
            $categoryIds[] = $category['id'];
        }

        return $categoryIds;
    }
}
