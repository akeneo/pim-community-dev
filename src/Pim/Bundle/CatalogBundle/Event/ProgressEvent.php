<?php

namespace Pim\Bundle\CatalogBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Represents a progress event for a task
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProgressEvent extends Event
{
    /**
     * @var string
     */
    protected $section;

    /**
     * @var int
     */
    protected $totalItems;

    /**
     * @var int
     */
    protected $treatedItems;

    /**
     * Constructor
     *
     * @param int    $totalItems
     * @param int    $treatedItems
     * @param string $section
     */
    public function __construct($totalItems, $treatedItems, $section = '')
    {
        $this->totalItems = $totalItems;
        $this->treatedItems = $treatedItems;
        $this->section = $section;
    }

    /**
     * Returns the section name
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Returns the number of items for the task
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Returns the number of treated items for the task
     *
     * @return int
     */
    public function getTreatedItems()
    {
        return $this->treatedItems;
    }
}
