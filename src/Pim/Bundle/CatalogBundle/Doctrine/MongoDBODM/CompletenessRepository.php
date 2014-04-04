<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\CompletenessRepositoryInterface;

use Doctrine\ODM\MongoDB\DocumentManager;

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
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @param DocumentManager    $documentManager
     * @param ChannelManager     $channelManager
     * @param CategoryRepository $categoryRepository
     * @param string             $productClass
     */
    public function __construct(
        DocumentManager $documentManager,
        ChannelManager $channelManager,
        CategoryRepository $categoryRepository,
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
                ->field('categories')->in($categoryIds)
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
                    ->field('categories')->in($categoryIds)
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
