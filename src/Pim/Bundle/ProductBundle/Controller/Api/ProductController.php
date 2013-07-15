<?php
namespace Pim\Bundle\ProductBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\UserBundle\Annotation\Acl;

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
        $locale = $this->getRequest()->get('locale');

        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($scope, $locale, $page, $limit);
    }

    /**
     * Get a single product
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get a single product",
     *      resource=true
     * )
     * @return Response
     */
    public function getAction($id)
    {
        $scope = $this->getRequest()->get('scope');
        $locale = $this->getRequest()->get('locale');

        return $this->handleGetRequest($scope, $locale, $id);
    }

    /**
     * Return a list of products
     *
     * @param string  $scope
     * @param string  $locale
     * @param integer $page
     * @param integer $limit
     *
     * @return Response
     */
    protected function handleGetListRequest($scope, $locale, $page, $limit)
    {
        return new Response();
    }

    /**
     * Return a single product
     *
     * @param string  $scope
     * @param string  $locale
     * @param integer $id
     *
     * @return Response
     */
    protected function handleGetRequest($scope, $locale, $id)
    {
        return new Response();
    }
}
