<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Add context in version data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContextListener implements EventSubscriberInterface
{
    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * Constructor
     *
     * @param VersionManager $versionManager
     */
    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_JOB_EXECUTION => 'addContext'
        );
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
            $this->versionManager->setContext(
                sprintf('%s "%s"', JobInstance::TYPE_IMPORT, $jobInstance->getCode())
            );
        }
    }
}
