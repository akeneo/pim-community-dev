<?php

namespace Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Hal\HalResource;
use Akeneo\Tool\Component\Api\Hal\Link;
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
            'limit'               => null,
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
        $this->resolver->setAllowedTypes('limit', ['int', 'null']);

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

        $limit = $parameters['query_parameters']['limit'] ?? $parameters['limit'];
        if (null === $limit) {
            throw new PaginationParametersException('The limit must be defined.');
        }

        $embedded = [];
        foreach ($items as $item) {
            $itemIdentifier = $item[$parameters['item_identifier_key']];
            $itemUriParameters = array_merge($parameters['uri_parameters'], ['code' => $itemIdentifier]);

            $itemLinks = [
                $this->createLink($parameters['item_route_name'], $itemUriParameters, null, 'self')
            ];

            $embedded[] = new HalResource($itemLinks, [], $item);
        }

        $uriParameters = array_merge($parameters['uri_parameters'], $parameters['query_parameters']);

        $links = [
            $this->createLink($parameters['list_route_name'], $uriParameters, $parameters['search_after']['self'], 'self'),
            $this->createLink($parameters['list_route_name'], $uriParameters, null, 'first'),
        ];

        if (count($items) === (int) $limit) {
            $links[] = $this->createLink(
                $parameters['list_route_name'],
                $uriParameters,
                $parameters['search_after']['next'],
                'next'
            );
        }

        $collection = new HalResource($links, ['items' => $embedded], []);

        return $collection->toArray();
    }

    /**
     * Create a link from a route name.
     *
     * @param string      $routeName
     * @param array       $parameters
     * @param string|null $searchAfterIdentifier
     * @param string      $linkName
     *
     * @return Link
     */
    protected function createLink($routeName, array $parameters, ?string $searchAfterIdentifier, string $linkName): Link
    {
        $parameters['search_after'] = $searchAfterIdentifier;

        $url =  $this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return new Link($linkName, $url);
    }
}
