<?php

namespace Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Hal\HalResource;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * HAL format paginator.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OffsetHalPaginator implements PaginatorInterface
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
            'list_route_name',
            'item_route_name',
        ]);

        $this->resolver->setAllowedTypes('uri_parameters', 'array');
        $this->resolver->setAllowedTypes('item_identifier_key', 'string');
        $this->resolver->setAllowedTypes('query_parameters', 'array');
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

        $data = ['current_page' => (int) $parameters['query_parameters']['page']];

        if (null !== $count) {
            $data['items_count'] = $count;
        }

        $embedded = [];
        foreach ($items as $item) {
            $itemIdentifier = $item[$parameters['item_identifier_key']];
            $itemUriParameters = array_merge($parameters['uri_parameters'], ['code' => $itemIdentifier]);

            $itemLinks = [
                $this->createLink($parameters['item_route_name'], $itemUriParameters, 'self')
            ];

            $embedded[] = new HalResource($itemLinks, [], $item);
        }

        $uriParameters = array_merge($parameters['uri_parameters'], $parameters['query_parameters']);

        $links = [
            $this->createLink($parameters['list_route_name'], $uriParameters, 'self'),
            $this->createFirstLink($parameters['list_route_name'], $uriParameters),
        ];

        $previousLink = $this->createPreviousLink($parameters['list_route_name'], $uriParameters);
        if (null !== $previousLink) {
            $links[] = $previousLink;
        }

        $nextLink = $this->createNextLink($parameters['list_route_name'], $uriParameters, $items);
        if (null !== $nextLink) {
            $links[] = $nextLink;
        }

        $collection = new HalResource($links, ['items' => $embedded], $data);

        return $collection->toArray();
    }

    /**
     * Create a link from a route name.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param string $linkName
     *
     * @return Link
     */
    protected function createLink($routeName, array $parameters, $linkName)
    {
        $url = $this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return new Link($linkName, $url);
    }

    /**
     * Create the link to the first page.
     *
     * @param string $routeName
     * @param array  $parameters
     *
     * @return Link
     */
    protected function createFirstLink($routeName, array $parameters)
    {
        $parameters['page'] = 1;

        return $this->createLink($routeName, $parameters, 'first');
    }

    /**
     * Create the link to the next page if it exists.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param array  $items
     *
     * @return Link|null return either a link to the next page or null if there is not a next page
     */
    protected function createNextLink($routeName, array $parameters, $items)
    {
        if (count($items) < (int) $parameters['limit']) {
            return null;
        }

        $parameters['page']++;

        return $this->createLink($routeName, $parameters, 'next');
    }

    /**
     * Create the link to the previous page if it exists.
     *
     * @param string $routeName
     * @param array  $parameters
     *
     * @return Link|null return either a link to the previous page or null if there is not a previous page
     */
    protected function createPreviousLink($routeName, array $parameters)
    {
        $currentPage = $parameters['page'];

        if ($currentPage < 2) {
            return null;
        }

        $parameters['page']--;

        return $this->createLink($routeName, $parameters, 'previous');
    }
}
