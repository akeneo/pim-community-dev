<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
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
        $jobInstance = $event->getSubject();
        $updatedData = $event->getArgument('data');

        if (!$jobInstance->isScheduled()) {
            return;
        }

        $currentAutomation = json_decode($jobInstance->getAutomation(), true);
        $updatedAutomation = $updatedData['automation'];

        if ($currentAutomation['cron_expression'] !== $updatedAutomation['cron_expression']) {
            $now = new \DateTime();
            $currentAutomation['setup_date'] = $now->format('Y-m-d H:i:s');
            $jobInstance->setAutomation(json_encode($updatedAutomation));
        }
    }
}
