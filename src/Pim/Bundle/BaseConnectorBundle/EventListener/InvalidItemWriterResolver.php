<?php

namespace Pim\Bundle\BaseConnectorBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidItemWriterResolver implements EventSubscriberInterface
{
    /** @var array */
    protected $writers = [];

    /** @var InvalidItemsCollector */
    protected $invalidItemsCollector;

    /**
     * @param InvalidItemsCollector $invalidItemsCollector
     */
    public function __construct(InvalidItemsCollector $invalidItemsCollector)
    {
        $this->invalidItemsCollector = $invalidItemsCollector;
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeStatusUpgrade',
        ];
    }

    /**
     * Register a writer
     *
     * TODO: Update the $writer interface
     *
     * @param ArchiverInterface $writer
     *
     * @throws \InvalidArgumentException
     */
    public function registerWriter(ArchiverInterface $writer)
    {
        if (array_key_exists($writer->getName(), $this->writers)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'There is already a registered writer named "%s": %s',
                    $writer->getName(),
                    get_class($this->writers[$writer->getName()])
                )
            );
        }

        $this->writers[$writer->getName()] = $writer;
    }

    /**
     * Delegate archiving to the registered archivers
     *
     * @param JobExecutionEvent $event
     */
    public function beforeStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $items = $this->invalidItemsCollector->getInvalidItems();

        foreach ($this->writers as $writer) {
            if ($writer->supports($items)) {
                $writer->archive($jobExecution);
            }
        }
    }
}
