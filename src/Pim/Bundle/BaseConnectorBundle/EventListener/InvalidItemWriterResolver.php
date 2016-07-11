<?php

namespace Pim\Bundle\BaseConnectorBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
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

        foreach ($this->writers as $writer) {
            if ($writer->supports($jobExecution)) {
                $writer->archive($jobExecution);
            }
        }
    }
    /**
     * Get the invalid item file
     *
     * @param JobExecution $jobExecution
     * @param string       $writer
     * @param string       $key
     *
     * @throws \InvalidArgumentException
     *
     * @return resource
     */
    public function getInvalidItemFile(JobExecution $jobExecution, $writer, $key)
    {
        if (!isset($this->writers[$writer])) {
            throw new \InvalidArgumentException(
                sprintf('Writer "%s" is not registered', $writer)
            );
        }

        return $this->writers[$writer]->getArchive($jobExecution, $key);
    }
}
