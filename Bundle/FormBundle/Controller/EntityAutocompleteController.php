<?php

namespace Oro\Bundle\FormBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;
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
        $name = $request->get('name');
        $query = $request->get('query');
        $page = intval($request->get('page', 1));
        $perPage = intval($request->get('per_page', 50));

        if (!$name) {
            throw new HttpException(400, 'Parameter "name" is required');
        }

        if ($page <= 0) {
            throw new HttpException(400, 'Parameter "page" must be greater than 0');
        }

        if ($perPage <= 0) {
            throw new HttpException(400, 'Parameter "per_page" must be greater than 0');
        }

        $searchHandler = $this->get('oro_form.autocomplete.search_registry')->getSearchHandler($name);

        return new JsonResponse($searchHandler->search($query, $page, $perPage));
    }
}
