<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Allows to remove relevant projects in terms of an other entity.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectRemoverEngine
{
    /** @var ChainedProjectRemoverRule */
    protected $chainedProjectRemover;

    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ObjectRepository */
    protected $projectRepository;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /**
     * @param ChainedProjectRemoverRule $chainedProjectRemover
     * @param ObjectRepository          $projectRepository
     * @param RemoverInterface          $projectRemover
     * @param ObjectDetacherInterface   $detacher
     */
    public function __construct(
        ChainedProjectRemoverRule $chainedProjectRemover,
        ObjectRepository $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->chainedProjectRemover = $chainedProjectRemover;
        $this->projectRepository = $projectRepository;
        $this->projectRemover = $projectRemover;
        $this->detacher = $detacher;
    }

    /**
     * Allows to remove relevant projects in terms of an other entity.
     *
     * @param mixed $entity
     */
    public function remove($entity)
    {
        foreach ($this->projectRepository->findAll() as $project) {
            if ($this->chainedProjectRemover->hasToBeRemoved($project, $entity)) {
                $this->projectRemover->remove($project);
            } else {
                $this->detacher->detach($project);
            }
        }
    }
}
