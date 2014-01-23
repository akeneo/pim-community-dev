<?php

namespace Pim\Bundle\WebServiceBundle\Controller\Rest;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Product API controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @RouteResource("product")
 * @NamePrefix("oro_api_")
 */
class ProductController extends FOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /**
     * Get all products
     *
     * @QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Number of products per page. defaults to 10."
     * )
     * @ApiDoc(
     *     description="Get all products",
     *     resource=true
     * )
     * filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * @return Response
     */
    public function cgetAction()
    {
        $scope = $this->getRequest()->get('scope');

        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($scope, $page, $limit);
    }

    /**
     * Get a single product
     *
     * @param string $identifier
     *
     * @ApiDoc(
     *      description="Get a single product",
     *      resource=true
     * )
     * @return Response
     */
    public function getAction($identifier)
    {
        $scope = $this->getRequest()->get('scope');

        return $this->handleGetRequest($scope, $identifier);
    }

    /**
     * Return a list of products
     *
     * @param string  $scope
     * @param integer $page
     * @param integer $limit
     *
     * @return Response
     */
    protected function handleGetListRequest($scope, $page, $limit)
    {
        $manager = $this->get('pim_catalog.manager.product');
        $manager->setScope($scope);

        $offset = --$page * $limit;

        $products = $manager->getFlexibleRepository()->findBy([], ['id' => 'ASC'], $limit, $offset);

        $channels = $this->get('pim_catalog.manager.channel')->getChannels(['code' => $scope]);
        $channel = reset($channels);

        if (!$channel) {
            throw new \LogicException('Channel not found');
        }

        $normalizer = $this->get('pim_serializer.normalizer.product');
        $normalizer->setChannel($channel);

        $serializer = $this->get('pim_serializer');
        $products = $serializer->serialize($products, 'json');

        return new Response($products);
    }

    /**
     * Return a single product
     *
     * @param string $scope
     * @param string $identifier
     *
     * @return Response
     */
    protected function handleGetRequest($scope, $identifier)
    {
        $manager = $this->get('pim_catalog.manager.product');
        $manager->setScope($scope);

        $product = $manager->findByIdentifier($identifier);

        if (!$product) {
            return new Response('', 404);
        }

        $channels = $this->get('pim_catalog.manager.channel')->getChannels(['code' => $scope]);
        $channel = reset($channels);

        if (!$channel) {
            throw new \LogicException('Channel not found');
        }

        $normalizer = $this->get('pim_serializer.normalizer.product');
        $normalizer->setChannel($channel);

        $serializer = $this->get('pim_serializer');
        $product = $serializer->serialize($product, 'json');

        return new Response($product);
    }
}
