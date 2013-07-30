<?php

namespace Oro\Bundle\SearchBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\SearchBundle\Query\Result\Item;

class PrepareResultItemEvent extends Event
{
    /**
     * Event name
     * @const string
     */
    const EVENT_NAME = 'oro_search.prepare_result_item';

    /**
     * @var \Oro\Bundle\SearchBundle\Query\Result\Item
     */
    protected $resultItem;

    /**
     * @var Object
     */
    protected $entityObject;

    public function __construct(Item $item, $entityObject = null)
    {
        $this->resultItem = $item;
        $this->entityObject = $entityObject;
    }

    /**
     * Getter for result item
     *
     * @return Item
     */
    public function getResultItem()
    {
        return $this->resultItem;
    }

    /**
     * Getter for entity object
     *
     * @return Object
     */
    public function getEntity()
    {
        return $this->entityObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }
}
