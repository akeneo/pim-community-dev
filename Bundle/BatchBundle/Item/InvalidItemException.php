<?php

namespace Oro\Bundle\BatchBundle\Item;

/**
 * Exception throw during step execution when an item is invalid
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemException extends \Exception
{
    /** @var array */
    protected $item;

    /**
     * Constructor
     *
     * @param string $message
     * @param array  $item
     */
    public function __construct($message, array $item)
    {
        parent::__construct($message);

        $this->item = $item;
    }

    /**
     * Get the invalid item
     */
    public function getItem()
    {
        return $this->item;
    }
}
