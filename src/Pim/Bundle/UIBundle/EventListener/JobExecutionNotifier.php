<?php

namespace Pim\Bundle\UIBundle\EventListener;

use Pim\Bundle\UIBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;

/**
 *
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @var NotificationManager */
    protected $manager;

    /** @var UserContext */
    protected $context;

    /**
     * @param NotificationManager $manager
     * @param UserContext         $context
     */
    public function __construct(NotificationManager $manager, UserContext $context)
    {
        $this->manager = $manager;
        $this->context = $context;
    }

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
     *
     * @param JobExecutionEvent $event
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $user = $this->context->getUser();
        $jobExecution = $event->getJobExecution();

//        if (null === $user) {
//            return;
//        }

        $this->manager->notify([$user], 'Some message');
    }
}
