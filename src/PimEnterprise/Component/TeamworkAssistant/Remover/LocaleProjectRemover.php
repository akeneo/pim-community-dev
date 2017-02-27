<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class LocaleProjectRemover implements ProjectRemoverInterface
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
     * A project has to be removed if its locale is now deactivated or if its locale is no longer part
     * of its channel locales.
     *
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($locale, $action = null)
    {
        foreach ($this->projectRepository->findByLocale($locale) as $project) {
            if ($this->hasToBeRemoved($project, $locale)) {
                $this->projectRemover->remove($project);
            } else {
                $this->detacher->detach($project);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($locale, $action = null)
    {
        return $locale instanceof LocaleInterface && StorageEvents::POST_SAVE === $action;
    }

    /**
     * @param ProjectInterface $project
     * @param LocaleInterface  $locale
     *
     * @return bool
     */
    protected function hasToBeRemoved(ProjectInterface $project, LocaleInterface $locale)
    {
        if (!$locale->isActivated()) {
            return true;
        }

        $localeCode = $locale->getCode();
        $channelLocalesCode = $project->getChannel()->getLocaleCodes();
        if (!in_array($localeCode, $channelLocalesCode)) {
            return true;
        }

        return false;
    }
}
