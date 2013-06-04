<?php

namespace Oro\Bundle\FormBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;
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

        if ($page < 1) {
            throw new HttpException(400, 'Parameter "page" must be greater than 1');
        }

        if ($perPage <= 0) {
            throw new HttpException(400, 'Parameter "per_page" must be greater than 0');
        }

        $options = $this->getConfiguration()->getAutocompleteOptions($name);
        $searchHandler = $this->getSearchFactory()->create($options);

        if (isset($options['acl_resource']) && !$this->getAclManager()->isResourceGranted($options['acl_resource'])) {
            throw new AccessDeniedException('Access denied.');
        }

        $perPage = $perPage + 1;
        $results = $searchHandler->search($query, ($page - 1) * $perPage, $perPage);
        $hasMore = count($results) == $perPage;
        if ($hasMore) {
            $results = array_slice($results, 0, $perPage - 1);
        }

        return $this->render($options['view'], array(
                'results' => $this->transformEntities($name, $results),
                'options' => $options,
                'query' => $query,
                'page' => $page,
                'perPage' => $perPage - 1,
                'hasMore' => $hasMore,
            )
        );
    }

    protected function transformEntities($name, array $entities)
    {
        $result = array();
        /** @var $transformer \Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface */
        $transformer = $this->get('oro_form.autocomplete.transformer.entity_to_text');
        foreach ($entities as $entity) {
            $result[] = array(
                'id' => $entity->getId(),
                'text' => $transformer->transform($name, $entity)
            );
        }
        return $result;
    }

    /**
     * @return SearchFactoryInterface
     */
    protected function getSearchFactory()
    {
        return $this->get('oro_form.autocomplete.search_factory');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->get('oro_form.autocomplete.configuration');
    }

    /**
     * @return Manager
     */
    public function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }
}
