<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller;

use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DataProviderFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Suggest data REST controller to interact between UI
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SuggestDataController
{
    /** @var ProductRepositoryInterface */
    protected $repository;

    /** @var DataProviderFactory */
    protected $dataProviderFactory;

    /**
     * @param ProductRepositoryInterface $repository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->repository = $repository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    public function pushAction($productId)
    {
        $product = $this->repository->find($productId);
        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product id "%s" not found', $productId)
            );
        }

        $dataProvider = $this->dataProviderFactory->create();
        $dataProvider->push($product);
    }
}
