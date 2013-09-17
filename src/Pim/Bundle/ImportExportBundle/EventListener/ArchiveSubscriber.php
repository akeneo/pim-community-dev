<?php

namespace Pim\Bundle\ImportExportBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Event\StepExecutionEvent;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\ImportExportBundle\Reader\CsvReader;
use Pim\Bundle\ImportExportBundle\Writer\FileWriter;

/**
 * Subscriber to archive job execution files used as input and output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchiveSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        );
    }

    /**
     * Archive input and ouput files after the job execution
     *
     * @param JobExecutionEvent $event
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $jobInstance  = $jobExecution->getJobInstance();
        $job          = $jobInstance->getJob();
        $type         = $jobInstance->getType();
        $dirsep       = DIRECTORY_SEPARATOR;
        $path         = '/tmp/'.$type.$dirsep.$jobInstance->getAlias().$dirsep.$jobExecution->getId().$dirsep;

        // TODO use %kernel.root_dir% 
        foreach ($job->getSteps() as $step) {
            $reader = $step->getReader();
            $writer = $step->getWriter();

            if ($reader instanceof CsvReader) {
                mkdir($path, 755, true);
                copy($reader->getFilePath(), $path.'input.csv');
            }

            if ($writer instanceof FileWriter) {
                mkdir($path, 755, true);
                copy($writer->getPath(), $path.'output.csv');
            }
        }
    }
}

