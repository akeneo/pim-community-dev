<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class UpdateJobInstanceAutomationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            JobInstanceEvents::PRE_SAVE => 'updateJobInstanceAutomationSetupDate',
        ];
    }

    public function updateJobInstanceAutomationSetupDate(GenericEvent $event)
    {
        $data = $event->getArgument('data');
        $newParameters = $data['raw_parameters'];

        if (!isset($newParameters['automation']['cron_expression'])) {
            return;
        }

        /** @var JobInstance $jobInstance */
        $jobInstance = $event->getSubject();
        $currentParameters = $jobInstance->getRawParameters();

        $cronExpressionChanged = !isset($currentParameters['automation']['cron_expression'])
            || $currentParameters['automation']['cron_expression'] !== $newParameters['automation']['cron_expression'];

        if ($cronExpressionChanged) {
            $now = new \DateTime();
            $currentParameters['automation']['setup_date'] = $now->format('Y-m-d H:i:s');
            $jobInstance->setRawParameters($currentParameters);
        }
    }
}
