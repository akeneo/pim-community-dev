<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Repository\CompletenessRepositoryInterface;

/**
 * Completeness Repository for ODM
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRepository implements CompletenessRepositoryInterface
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @param DocumentManager             $documentManager
     * @param ChannelManager              $channelManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param string                      $productClass
     */
    public function __construct(
        DocumentManager $documentManager,
        ChannelManager $channelManager,
        CategoryRepositoryInterface $categoryRepository,
        $productClass
    ) {
        $this->documentManager    = $documentManager;
        $this->channelManager     = $channelManager;
        $this->categoryRepository = $categoryRepository;
        $this->productClass       = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountPerChannels()
    {
        $channels = $this->channelManager->getFullChannels();
        $productRepo = $this->documentManager->getRepository($this->productClass);

        $productsCount = array();
        foreach ($channels as $channel) {
            $category = $channel->getCategory();
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, true);
            $categoryIds = $this->categoryRepository->getCategoryIds($category, $categoryQb);

            $qb = $productRepo->createQueryBuilder()
                ->hydrate(false)
                ->field('categoryIds')->in($categoryIds)
                ->field('enabled')->equals(true)
                ->select('_id');

            $productsCount[] = [
                'label' => $channel->getLabel(),
                'total' => $qb->getQuery()->execute()->count()
            ];
        }

        return $productsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompleteProductsCountPerChannels()
    {
        $channels = $this->channelManager->getFullChannels();
        $productRepo = $this->documentManager->getRepository($this->productClass);

        $productsCount = array();
        foreach ($channels as $channel) {
            $category = $channel->getCategory();
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, true);
            $categoryIds = $this->categoryRepository->getCategoryIds($category, $categoryQb);

            foreach ($channel->getLocales() as $locale) {
                $data = array();
                $compSuffix = $channel->getCode().'-'.$locale->getCode();

                $qb = $productRepo->createQueryBuilder()
                    ->hydrate(false)
                    ->field('categoryIds')->in($categoryIds)
                    ->field('enabled')->equals(true)
                    ->field('normalizedData.completenesses.'.$compSuffix)
                    ->equals(100)
                    ->select('_id');

                $localeCount = $qb->getQuery()->execute()->count();
                $data['locale'] = $locale->getCode();
                $data['label'] = $channel->getLabel();
                $data['total'] = $localeCount;

                $productsCount[] = $data;
            }
        }

        return $productsCount;
    }
}
