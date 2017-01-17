<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\CategoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CategoryProjectRemover implements ProjectRemoverInterface
{
    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ObjectRepository */
    protected $projectRepository;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /**
     * @param ObjectRepository        $projectRepository
     * @param RemoverInterface        $projectRemover
     * @param ObjectDetacherInterface $detacher
     */
    public function __construct(
        ObjectRepository $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectRemover = $projectRemover;
        $this->detacher = $detacher;
    }

    /**
     * A project must be removed if a category used as product filter is removed.
     *
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($category, $action = null)
    {
        $categoryCode = $category->getCode();
        foreach ($this->projectRepository->findAll() as $project) {
            if ($this->hasToBeRemoved($project, $categoryCode)) {
                $this->projectRemover->remove($project);
            } else {
                $this->detacher->detach($project);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($category, $action = null)
    {
        return $category instanceof CategoryInterface && StorageEvents::PRE_REMOVE === $action;
    }

    /**
     * @param ProjectInterface $project
     * @param string           $categoryCode
     *
     * @return bool
     */
    protected function hasToBeRemoved(ProjectInterface $project, $categoryCode)
    {
        $filters = $project->getProductFilters();
        foreach ($filters as $filter) {
            if ('categories' === $filter['field'] && in_array($categoryCode, $filter['value'])) {
                return true;
            }
        }

        return false;
    }
}
