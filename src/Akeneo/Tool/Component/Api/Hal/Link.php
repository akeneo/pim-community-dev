<?php

namespace Akeneo\Tool\Component\Api\Hal;

/**
 * Basic implementation of a HAL link.
 *
 * Only one link can be associated to a relationship. This property differs from the HAL specification where you can add
 * either a single link to a relationship or an array of links.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Link
{
    /** @var string */
    protected $rel;

    /** @var string */
    protected $url;

    /**
     * @param string $rel
     * @param string $url
     */
    public function __construct($rel, $url)
    {
        $this->rel = $rel;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Generate the link into an array with the HAL format.
     *
     * [
     *     'link_name' => [
     *         'href' => 'http://akeneo.com/api/resource/id',
     *     ],
     * ]
     *
     * @return array
     */
    public function toArray()
    {
        $link[$this->rel]['href'] = $this->url;

        return $link;
    }
}
