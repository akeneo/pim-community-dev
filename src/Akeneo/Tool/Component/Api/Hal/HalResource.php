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
     *
     * @return array
     */
    public function getEmbedded()
    {
        return $this->embedded;
    }

    /**
     * Get the links of the resource.
     *
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Get the data.
     *
     * @return array
     */
    public function getData()
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
     *               '_links' => [
     *                   'self' => [
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
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        $links = $this->normalizeLinks($this->links);
        if (!empty($links)) {
            $data['_links'] = $links;
        }

        foreach ($this->data as $key => $value) {
            if (isset($data[$key]) && is_array($value)) {
                $data[$key] = array_merge($data[$key], $value);
            } else {
                $data[$key] = $value;
            }
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
     *
     * @return array
     */
    protected function normalizeEmbedded(array $embeddedItems)
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
     *
     * @return array
     */
    protected function normalizeLinks(array $links)
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
    protected function addLink(Link $link)
    {
        $this->links[] = $link;
    }

    /**
     * Add a resource in the list of embedded resources for a given key.
     *
     * @param string      $key      key of the list
     * @param HalResource $resource resource to add
     */
    protected function addEmbedded($key, HalResource $resource)
    {
        $this->embedded[$key][] = $resource;
    }

    /**
     * Set the list of embedded resources for a given key.
     *
     * @param string     $key       key of the list
     * @param Resource[] $resources array of resources
     */
    protected function setEmbedded($key, array $resources)
    {
        $this->embedded[$key] = [];

        foreach ($resources as $resource) {
            $this->addEmbedded($key, $resource);
        }
    }
}
