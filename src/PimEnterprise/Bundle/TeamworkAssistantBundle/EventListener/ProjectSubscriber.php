<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectIdentifier;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Manage all actions related to the project
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectSubscriber implements EventSubscriberInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /**
     * @param PreProcessingRepositoryInterface $preProcessingRepository
     */
    public function __construct(PreProcessingRepositoryInterface $preProcessingRepository)
    {
        $this->preProcessingRepository = $preProcessingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE   => 'generateCode',
            StorageEvents::PRE_REMOVE => 'removePreProcessedEntries',
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

        $projectCode = new ProjectIdentifier(
            $project->getLabel(),
            $project->getChannel()->getCode(),
            $project->getLocale()->getCode()
        );

        $project->setCode($projectCode);
        $datagridView->setLabel((string)$projectCode);
    }

    /**
     * @param GenericEvent $event
     */
    public function removePreProcessedEntries(GenericEvent $event)
    {
        $project = $event->getSubject();
        if (!$project instanceof ProjectInterface) {
            return;
        }

        $this->preProcessingRepository->remove($project);
    }
}
