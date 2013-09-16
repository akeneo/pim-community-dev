<?php

namespace Oro\Bundle\SearchBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SearchBundle\Datagrid\SearchDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;
use Oro\Bundle\SearchBundle\Provider\ResultStatisticsProvider;
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Acl(
 *     id="oro_search",
 *     name="Search",
 *     description="Search",
 *     parent="oro_security"
 * )
 */
class SearchController extends Controller
{
    /**
     * @Route("/advanced-search", name="oro_search_advanced")
     *
     * @Acl(
     *     id="oro_search_ajax_advanced",
     *     name="Ajax advanced search",
     *     description="Ajax advanced search",
     *     parent="oro_search"
     * )
     */
    public function ajaxAdvancedSearchAction()
    {
        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse(
                $this->get('oro_search.index')->advancedSearch(
                    $this->getRequest()->get('query')
                )->toSearchResultData()
            )
            : $this->forward('OroSearchBundle:Search:searchResults');
    }

    /**
     * Show search block
     *
     * @Route("/search-bar", name="oro_search_bar")
     * @Template("OroSearchBundle:Search:searchBar.html.twig")
     *
     * @Acl(
     *     id="oro_search_bar",
     *     name="Search bar",
     *     description="Search bar",
     *     parent="oro_search"
     * )
     */
    public function searchBarAction()
    {
        return array(
            'entities'     => $this->get('oro_search.index')->getEntitiesLabels(),
            'searchString' => $this->getRequest()->get('searchString'),
            'fromString'   => $this->getRequest()->get('fromString'),
        );
    }

    /**
     * @param  string       $from
     * @param  string       $string
     * @return DatagridView
     *
     * @Acl(
     *     id="oro_search_datagrid_results",
     *     name="Search datagrid results",
     *     description="Search datagrid results",
     *     parent="oro_search"
     * )
     */
    protected function getSearchResultsDatagridView($from, $string)
    {
        /** @var $datagridManager SearchDatagridManager */
        $datagridManager = $this->get('oro_search.datagrid_results.datagrid_manager');

        $datagridManager->setSearchEntity($from);
        $datagridManager->setSearchString($string);
        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'from'   => $from,
                'search' => $string,
            )
        );

        return $datagridManager->getDatagrid()->createView();
    }

    /**
     * Show search results
     *
     * @Route("/", name="oro_search_results")
     * @Template("OroSearchBundle:Search:searchResults.html.twig")
     *
     * @Acl(
     *     id="oro_search_results",
     *     name="Search results",
     *     description="Search results",
     *     parent="oro_search"
     * )
     */
    public function searchResultsAction(Request $request)
    {
        $from   = $request->get('from');
        $string = $request->get('search');

        $datagridView = $this->getSearchResultsDatagridView($from, $string);

        /** @var $resultProvider ResultStatisticsProvider */
        $resultProvider = $this->get('oro_search.provider.result_statistics_provider');

        return array(
            'from'           => $from,
            'searchString'   => $string,
            'groupedResults' => $resultProvider->getGroupedResults($string),
            'datagrid'       => $datagridView
        );
    }

    /**
     * Return search results in json for datagrid
     *
     * @Route("/ajax", name="oro_search_results_ajax")
     *
     * @Acl(
     *     id="oro_search_ajax_results",
     *     name="Ajax search results",
     *     description="Ajax search results",
     *     parent="oro_search"
     * )
     */
    public function searchResultsAjaxAction(Request $request)
    {
        $from   = $request->get('from');
        $string = $request->get('search');

        $datagridView = $this->getSearchResultsDatagridView($from, $string);

        return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
    }
}
