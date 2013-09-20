<?php

namespace Oro\Bundle\SearchBundle\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @RouteResource("search")
 * @NamePrefix("oro_api_")
 *
 * @Acl(
 *     id="oro_search_api",
 *     name="Search API",
 *     description="Search API",
 *     parent="oro_search"
 * )
 */
class SearchController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  description="Get search result",
     *  resource=true,
     *  filters={
     *      {"name"="search", "dataType"="string"},
     *      {"name"="offset", "dataType"="integer"},
     *      {"name"="max_results", "dataType"="integer"},
     *      {"name"="from", "dataType"="string"}
     *  }
     * )
     *
     * @Acl(
     *     id="oro_search_api_feature",
     *     name="API for search",
     *     description="API for search",
     *     parent="oro_search_api"
     * )
     */
    public function getAction()
    {
        return $this->handleView(
            $this->view(
                $this->get('oro_search.index')->simpleSearch(
                    $this->getRequest()->get('search'),
                    (int) $this->getRequest()->get('offset'),
                    (int) $this->getRequest()->get('max_results'),
                    $this->getRequest()->get('from')
                )->toSearchResultData(),
                Codes::HTTP_OK
            )->setTemplate('OroSearchBundle:Search:searchSuggestion.html.twig')
        );
    }
}
