<?php

namespace Oro\Bundle\FormBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Acl\Manager;

/**
 * @Route("/autocomplete")
 */
class EntityAutocompleteController extends Controller
{
    /**
     * @Route("/search", name="oro_form_autocomplete_search")
     */
    public function searchAction(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type');

        $searchRegistry = $this->get('oro_form.autocomplete.search_registry');
        $searchHandler = $searchRegistry->getSearchHandler($type);

        return new Response('OK!');
    }

    /**
     * @return Manager
     */
    public function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }
}
