<?php

namespace Pim\Component\Api\Pagination;

use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Hal\HalResource;
use Pim\Component\Api\Hal\Link;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Paginate with an HAL representation a list of resources, based on a search after research.
 * Search after research does not expose previous and last links.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterHalPaginator implements PaginatorInterface
{
    /** @var RouterInterface */
    protected $router;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setDefaults([
            'uri_parameters'      => [],
            'item_identifier_key' => 'code',
        ]);

        $this->resolver->setRequired([
            'query_parameters',
            'search_after',
            'list_route_name',
            'item_route_name',
        ]);

        $this->resolver->setAllowedTypes('uri_parameters', 'array');
        $this->resolver->setAllowedTypes('item_identifier_key', 'string');
        $this->resolver->setAllowedTypes('query_parameters', 'array');
        $this->resolver->setAllowedTypes('search_after', 'array');
        $this->resolver->setAllowedTypes('list_route_name', 'string');
        $this->resolver->setAllowedTypes('item_route_name', 'string');

        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(array $items, array $parameters, $count)
    {
        try {
            $parameters = $this->resolver->resolve($parameters);
        } catch (\InvalidArgumentException $e) {
            throw new PaginationParametersException($e->getMessage(), $e->getCode(), $e);
        }

        $embedded = [];
        foreach ($items as $item) {
            $itemIdentifier = $item[$parameters['item_identifier_key']];
            $itemUriParameters = array_merge($parameters['uri_parameters'], ['code' => $itemIdentifier]);

            $itemLinks = [
                $this->createLink($parameters['item_route_name'], $itemUriParameters, null, null, 'self')
            ];

            $embedded[] = new HalResource($itemLinks, [], $item);
        }

        $uriParameters = array_merge($parameters['uri_parameters'], $parameters['query_parameters']);

        $searchAfter = isset($uriParameters['search_after']) ? $uriParameters['search_after'] : null;
        $searchBefore = isset($uriParameters['search_before']) ? $uriParameters['search_before'] : null;
        $links = [
            $this->createLink($parameters['list_route_name'], $uriParameters, $searchAfter, $searchBefore, 'self'),
            $this->createLink($parameters['list_route_name'], $uriParameters, null, null, 'first')
        ];

        if (isset($uriParameters['search_after']) || isset($uriParameters['search_before'])) {
            $links[] = $this->createLink(
                $parameters['list_route_name'],
                $uriParameters,
                null,
                $parameters['search_after']['previous'],
                'previous'
            );
        }

        if (count($items) === (int) $parameters['query_parameters']['limit']) {
            $links[] = $this->createLink(
                $parameters['list_route_name'],
                $uriParameters,
                $parameters['search_after']['next'],
                null,
                'next'
            );
        }

        $data = ['current_page' => null];
        if (null !== $count) {
            $data['items_count'] = $count;
        }

        $collection = new HalResource($links, ['items' => $embedded], $data);

        return $collection->toArray();
    }

    /**
     * Create a link from a route name.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param string $searchAfter
     * @param string $searchBefore
     * @param string $linkName
     *
     * @return Link
     */
    protected function createLink($routeName, array $parameters, $searchAfter, $searchBefore, $linkName)
    {
        $parameters['search_after'] = $searchAfter;
        $parameters['search_before'] = $searchBefore;

        $url =  $this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return new Link($linkName, $url);
    }
}
