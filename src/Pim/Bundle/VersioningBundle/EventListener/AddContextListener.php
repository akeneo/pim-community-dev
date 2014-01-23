<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;

/**
 * Add context in audit data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContextListener implements EventSubscriberInterface
{
    /**
     * @var AuditBuilder
     */
    protected $auditBuilder;

    /**
     * Constructor
     *
     * @param AuditBuilder $builder
     */
    public function __construct($builder)
    {
        $this->auditBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'addContext'
        ];
    }

    /**
     * Add context in data audit builder
     *
     * @param JobExecutionEvent $event
     */
    public function addContext(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $jobInstance  = $jobExecution->getJobInstance();
        if ($jobInstance->getType() === JobInstance::TYPE_IMPORT) {
            $this->auditBuilder->setContext(
                sprintf('%s "%s"', JobInstance::TYPE_IMPORT, $jobInstance->getCode())
            );
        }
    }
}
