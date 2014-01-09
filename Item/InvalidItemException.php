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

    /** @var array */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param string $message
     * @param array  $item
     * @param array  $messageParameters
     */
    public function __construct($message, array $item, array $messageParameters = array())
    {
        parent::__construct($message);

        $this->item              = $item;
        $this->messageParameters = $messageParameters;
    }

    /**
     * Get message parameters
     *
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * Get the invalid item
     *
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }
}
