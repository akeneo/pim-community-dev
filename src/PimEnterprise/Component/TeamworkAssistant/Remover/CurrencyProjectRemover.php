<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CurrencyProjectRemover implements ProjectRemoverInterface
{
    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     * @param RemoverInterface           $projectRemover
     * @param ObjectDetacherInterface    $detacher
     */
    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectRemover = $projectRemover;
        $this->detacher = $detacher;
    }

    /**
     * A project is removed if it used a currency as product filter that is removed from its channel.
     *
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($channel, $action = null)
    {
        foreach ($this->projectRepository->findByChannel($channel) as $project) {
            if ($this->hasToBeRemoved($project, $channel)) {
                $this->projectRemover->remove($project);
            } else {
                $this->detacher->detach($project);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($channel, $action = null)
    {
        return $channel instanceof ChannelInterface && StorageEvents::POST_SAVE === $action;
    }

    /**
     * Defines if a project has to be removed.
     *
     * @param ProjectInterface $project
     * @param ChannelInterface $channel
     *
     * @return bool
     */
    protected function hasToBeRemoved(ProjectInterface $project, ChannelInterface $channel)
    {
        $currencies = $channel->getCurrencies()->map(function (CurrencyInterface $currency) {
            return $currency->getCode();
        });

        foreach ($project->getProductFilters() as $filter) {
            if (isset($filter['value']['currency']) && !$currencies->contains($filter['value']['currency'])) {
                return true;
            }
        }

        return false;
    }
}
