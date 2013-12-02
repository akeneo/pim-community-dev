<?php

namespace Oro\Bundle\FormBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\FormBundle\Autocomplete\Security;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;

/**
 * @Route("/autocomplete")
 */
class AutocompleteController extends Controller
{
    /**
     * @Route("/config", name="oro_form_autocomplete_config")
     */
    public function configAction(Request $request)
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $config      = Yaml::parse($fileLocator->locate('config_icon.yml'));
        $query       = $request->get('query');
        $result      = array('results' => array());

        if ($query) {
            $data = array_filter(
                array_flip($config['oro_icon_select']),
                function ($item) use ($query) {
                    return strpos($item, $query) === 0;
                }
            );
            foreach (array_flip($data) as $key => $value) {
                $result['results'][] = array(
                    'id'   => $value,
                    'text' => $key
                );
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/search", name="oro_form_autocomplete_search")
     * AclAncestor("oro_search")
     */
    public function searchAction(Request $request)
    {
        $name    = $request->get('name');
        $query   = $request->get('query');
        $page    = intval($request->get('page', 1));
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

        if (!$this->get('oro_form.autocomplete.security')->isAutocompleteGranted($name)) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        /** @var SearchHandlerInterface $searchHandler */
        $searchHandler = $this->get('oro_form.autocomplete.search_registry')->getSearchHandler($name);

        return new JsonResponse($searchHandler->search($query, $page, $perPage));
    }
}
