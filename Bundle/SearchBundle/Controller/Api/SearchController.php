<?php

namespace Oro\Bundle\SearchBundle\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @RouteResource("search")
 * @NamePrefix("oro_api_")
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
     * @AclAncestor("oro_search")
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
