<?php

namespace Pim\Bundle\Batch2Bundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\Batch2Bundle\Event\ItemEvent;
use Pim\Bundle\Batch2Bundle\Event\EventInterface;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobDataCollector implements EventSubscriberInterface
{
    private $readItemCount = 0;
    private $readItemStatuses = array();
    private $processedItemCount = 0;
    private $processedItemStatuses = array();
    private $writtenItemCount = 0;
    private $writtenItemStatuses = array();
    private $statuses = array(
        ItemEvent::PASSED    => 'passed',
        ItemEvent::SKIPPED   => 'skipped',
        ItemEvent::PENDING   => 'pending',
        ItemEvent::UNDEFINED => 'undefined',
        ItemEvent::FAILED    => 'failed'
    );

    public function __construct()
    {
        $this->readItemStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->processedItemStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->writtenItemStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_READ    => 'afterRead',
            EventInterface::AFTER_PROCESS => 'afterProcess',
            EventInterface::AFTER_WRITE   => 'afterWrite',
        );
    }

    public function afterRead(ItemEvent $event)
    {
        ++$this->readItemCount;
        ++$this->readItemStatuses[$this->statuses[$event->getResult()]];
    }

    public function afterProcess(ItemEvent $event)
    {
        ++$this->processedItemCount;
        ++$this->processedItemStatuses[$this->statuses[$event->getResult()]];
    }

    public function afterWrite(ItemEvent $event)
    {
        ++$this->writtenItemCount;
        ++$this->writtenItemStatuses[$this->statuses[$event->getResult()]];
    }
}
