<?php

namespace Akeneo\Tool\Component\Api\Hal;

/**
 * Basic implementation of a HAL resource.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HalResource
{
    /** @var array */
    protected $links = [];

    /** @var array */
    protected $embedded = [];

    /** @var array */
    protected $data = [];

    /**
     * @param Link[] $links    links of the resource
     * @param array  $embedded associative array where the key is the name of the relationship, and the value an array of
     *                         embedded resources for this key.
     *                         Example : ['items' => Resource[]]
     * @param array  $data     data associated to the resource
     */
    public function __construct(array $links, array $embedded, array $data)
    {
        $this->data = $data;

        foreach ($links as $link) {
            $this->addLink($link);
        }

        foreach ($embedded as $key => $resources) {
            $this->setEmbedded($key, $resources);
        }
    }

    /**
     * Get the array of embedded list of resources.
     */
    public function getEmbedded(): array
    {
        return $this->embedded;
    }

    /**
     * Get the links of the resource.
     *
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Get the data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Generate the resource into an array with the HAL format.
     *
     * [
     *     'data' => 'my_data',
     *     '_links'       => [
     *         'self'     => [
     *             'href' => 'http://akeneo.com/api/self/id',
     *         ],
     *     ],
     *     '_embedded' => [
     *         'items' => [
     *           [
     *               '_links'       => [
     *                   'self'     => [
     *                       'href' => 'http://akeneo.com/api/resource/id',
     *                   ],
     *                   'other' => [
     *                       'href' => 'http://akeneo.com/api/resource/other',
     *                   ]
     *               ],
     *               'data' => 'item_data',
     *           ],
     *         ],
     *     ],
     * ]
     */
    public function toArray(): array
    {
        $data = [];

        $links = $this->normalizeLinks($this->links);
        if (!empty($links)) {
            $data['_links'] = $links;
        }

        foreach ($this->data as $key => $value) {
            $data[$key] = isset($data[$key]) && is_array($value) ? array_merge($data[$key], $value) : $value;
        }

        foreach ($this->embedded as $rel => $embedded) {
            $data['_embedded'][$rel] = $this->normalizeEmbedded($embedded);
        }

        return $data;
    }

    /**
     * Normalize a list of embedded resources into an array.
     *
     * @param Resource[] $embeddedItems list of embedded resource
     */
    protected function normalizeEmbedded(array $embeddedItems): array
    {
        $data = [];
        foreach ($embeddedItems as $embeddedItem) {
            $data[] = $embeddedItem->toArray();
        }

        return $data;
    }

    /**
     * Normalize the links into an array.
     *
     * @param Link[] $links list of links
     */
    protected function normalizeLinks(array $links): array
    {
        $data = [];
        foreach ($links as $link) {
            $data = array_merge($data, $link->toArray());
        }

        return $data;
    }

    /**
     * Add a link in the resource.
     *
     * @param Link $link
     */
    protected function addLink(Link $link): void
    {
        $this->links[] = $link;
    }

    /**
     * Add a resource in the list of embedded resources for a given key.
     *
     * @param string      $key      key of the list
     * @param HalResource $resource resource to add
     */
    protected function addEmbedded(string $key, HalResource $resource): void
    {
        $this->embedded[$key][] = $resource;
    }

    /**
     * Set the list of embedded resources for a given key.
     *
     * @param string     $key       key of the list
     * @param Resource[] $resources array of resources
     */
    protected function setEmbedded(string $key, array $resources): void
    {
        $this->embedded[$key] = [];

        foreach ($resources as $resource) {
            $this->addEmbedded($key, $resource);
        }
    }
}
