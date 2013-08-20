<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\BatchBundle\Event\ItemEvent;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Event\JobEvent;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobDataCollector implements EventSubscriberInterface
{
    private $readItemCount = 0;
    private $readerExecutionCount = 0;
    private $readerExecutionStatuses = array();
    private $processorExecutionCount = 0;
    private $processorExecutionStatuses = array();
    private $writerExecutionCount = 0;
    private $writerExecutionStatuses = array();
    private $statuses = array(
        ItemEvent::PASSED    => 'passed',
        ItemEvent::UNDEFINED => 'undefined',
        ItemEvent::FAILED    => 'failed'
    );

    public function __construct()
    {
        $this->readerExecutionStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->processorExecutionStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->writerExecutionStatuses = array_combine(
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
        $item = $event->getItem();

        if (is_array($item)) {
            $this->readItemCount += count($item);
        } else {
            ++$this->readItemCount;
        }

        ++$this->readerExecutionCount;
        ++$this->readerExecutionStatuses[$this->statuses[$event->getResult()]];
    }

    public function afterProcess(ItemEvent $event)
    {
        ++$this->processorExecutionCount;
        ++$this->processorExecutionStatuses[$this->statuses[$event->getResult()]];
    }

    public function afterWrite(ItemEvent $event)
    {
        ++$this->writerExecutionCount;
        ++$this->writerExecutionStatuses[$this->statuses[$event->getResult()]];
    }

    public function getReadItemCount()
    {
        return $this->readItemCount;
    }

    public function getReaderExecutionCount()
    {
        return $this->readerExecutionCount;
    }

    public function getProcessorExecutionCount()
    {
        return $this->processorExecutionCount;
    }

    public function getWriterExecutionCount()
    {
        return $this->writerExecutionCount;
    }
}
