<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Gedmo\Sluggable\Util\Urlizer;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Manage all actions related to the project
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'generateCode',
        ];
    }

    /**
     * Generate the project code before saving it in database.
     *
     * @param GenericEvent $event
     */
    public function generateCode(GenericEvent $event)
    {
        $project = $event->getSubject();

        if (!$project instanceof ProjectInterface) {
            return;
        }

        $datagridView = $project->getDatagridView();

        $projectCode = Urlizer::transliterate(
            sprintf(
                '%s %s %s',
                $project->getLabel(),
                $project->getChannel()->getCode(),
                $project->getLocale()->getCode()
            )
        );

        $project->setCode($projectCode);
        $datagridView->setLabel($projectCode);
    }
}
