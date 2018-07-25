<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add context in version data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContextSubscriber implements EventSubscriberInterface
{
    /**
     * @var VersionContext
     */
    protected $versionContext;

    /**
     * Constructor
     *
     * @param VersionContext $versionContext
     */
    public function __construct(VersionContext $versionContext)
    {
        $this->versionContext = $versionContext;
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
     * Add context in version manager
     *
     * @param JobExecutionEvent $event
     */
    public function addContext(JobExecutionEvent $event)
    {
        $jobInstance = $event->getJobExecution()->getJobInstance();
        if ($jobInstance->getType() === JobInstance::TYPE_IMPORT) {
            $this->versionContext->addContextInfo(
                sprintf('%s "%s"', JobInstance::TYPE_IMPORT, $jobInstance->getCode())
            );
        }
    }
}
