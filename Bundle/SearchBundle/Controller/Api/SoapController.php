<?php

namespace Oro\Bundle\SearchBundle\Controller\Api;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Symfony\Component\DependencyInjection\ContainerAware;
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Acl(
 *     id="oro_search_api_soap",
 *     name="Search API for SOAP",
 *     description="Search API for SOAP",
 *     parent="oro_search"
 * )
 */
class SoapController extends ContainerAware
{
    /**
     * @Soap\Method("search")
     * @Soap\Param("search", phpType = "string")
     * @Soap\Param("offset", phpType = "int")
     * @Soap\Param("max_results", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\SearchBundle\Query\Result")
     *
     * @Acl(
     *     id="oro_search_api_soap_search",
     *     name="Search API for SOAP",
     *     description="Search API for SOAP",
     *     parent="oro_search_api_soap"
     * )
     */
    public function searchAction($search, $offset = 0, $max_results = 0)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_search.index')->simpleSearch(
                $search,
                $offset,
                $max_results
            )
        );
    }

    /**
     * @Soap\Method("advancedSearch")
     * @Soap\Param("query", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\SearchBundle\Query\Result")
     *
     * @Acl(
     *     id="oro_search_api_soap_advanced_search",
     *     name="Advanced search API for SOAP",
     *     description="Advanced search API for SOAP",
     *     parent="oro_search_api_soap"
     * )
     */
    public function advancedSearchAction($query)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_search.index')->advancedSearch(
                $query
            )
        );
    }
}
