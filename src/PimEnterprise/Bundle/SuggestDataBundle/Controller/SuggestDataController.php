<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Client\ClientInterface;

/**
 * Suggest data REST controller to interact between UI
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SuggestDataController
{
    /** @var ClientInterface */
    protected $repository;

    /**
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function pushAction($productId)
    {
        var_dump($productId);

        // Hydrates Product
        // (Roles ? Which permissions should I have on the product? Permissions on the categories, attribute groups, ?)

        $product = $this->repository->find($productId);
        var_dump((string) $product);



        // Prepares data to send (Adapter + Mapping)
        // Subscriber?

        // Sends data we want to subscribe on

        //$this->client->pushProduct();
    }
}
