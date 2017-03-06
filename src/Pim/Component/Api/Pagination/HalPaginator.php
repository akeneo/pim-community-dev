<?php

namespace Pim\Component\Api\Pagination;

use Pim\Component\Api\Hal\HalResource;
use Pim\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * HAL format paginator.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HalPaginator implements PaginatorInterface
{
    /** @var RouterInterface */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(array $items, array $parameters, $count, $listRouteName, $itemRouteName, $itemIdentifier)
    {
        $data = [
            'current_page' => (int) $parameters['page'],
            'pages_count'  => $this->getLastPage($parameters['limit'], $count),
            'items_count'  => $count,
        ];

        $embedded = [];
        foreach ($items as $item) {
            $resourceItem = $this->createResource($itemRouteName, ['code' => $item[$itemIdentifier]], $item);
            $embedded[] = $resourceItem;
        }

        $links = [
            $this->createFirstLink($listRouteName, $parameters),
            $this->createLastLink($listRouteName, $parameters, $count),
        ];

        $previousLink = $this->createPreviousLink($listRouteName, $parameters, $count);
        if (null !== $previousLink) {
            $links[] = $previousLink;
        }

        $nextLink = $this->createNextLink($listRouteName, $parameters, $count);
        if (null !== $nextLink) {
            $links[] = $nextLink;
        }

        $collection = $this->createResource($listRouteName, $parameters, $data, $links, ['items' => $embedded]);

        return $collection->toArray();
    }

    /**
     * Generate an absolute URL for a specific route based on the given parameters.
     *
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string
     */
    protected function getUrl($routeName, array $parameters)
    {
        return $this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Create a resource from a route name.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param array  $data
     * @param array  $embedded
     * @param array  $links
     *
     * @return HalResource
     */
    protected function createResource($routeName, array $parameters, array $data = [], array $embedded = [], array $links = [])
    {
        $url = $this->getUrl($routeName, $parameters);

        return new HalResource($url, $embedded, $links, $data);
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
        $url = $this->getUrl($routeName, $parameters);

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
     * Create the link to the last page.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param int    $count
     *
     * @return Link
     */
    protected function createLastLink($routeName, array $parameters, $count)
    {
        $parameters['page'] = $this->getLastPage($parameters['limit'], $count);

        return $this->createLink($routeName, $parameters, 'last');
    }

    /**
     * Create the link to the next page if it exists.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param int    $count
     *
     * @return Link|null return either a link to the next page or null if there is not a next page
     */
    protected function createNextLink($routeName, array $parameters, $count)
    {
        $lastPage = $this->getLastPage($parameters['limit'], $count);
        $nextPage = ++$parameters['page'];

        if ($nextPage > $lastPage) {
            return null;
        }

        return $this->createLink($routeName, $parameters, 'next');
    }

    /**
     * Create the link to the previous page if it exists.
     *
     * @param string $routeName
     * @param array  $parameters
     * @param int    $count
     *
     * @return Link|null return either a link to the previous page or null if there is not a previous page
     */
    protected function createPreviousLink($routeName, array $parameters, $count)
    {
        $lastPage    = $this->getLastPage($parameters['limit'], $count);
        $currentPage = $parameters['page'];

        if ($currentPage < 2 || $currentPage > $lastPage) {
            return null;
        }

        $parameters['page']--;

        return $this->createLink($routeName, $parameters, 'previous');
    }

    /**
     * Calculate the last page depending on the number of total items and the limit.
     *
     * @param int $limit
     * @param int $count
     *
     * @return int
     */
    protected function getLastPage($limit, $count)
    {
        return 0 === $count ? 1 : (int) ceil($count / $limit);
    }
}
