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

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ChannelProjectRemover implements ProjectRemoverInterface
{
    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     * @param RemoverInterface           $projectRemover
     */
    public function __construct(ProjectRepositoryInterface $projectRepository, RemoverInterface $projectRemover)
    {
        $this->projectRepository = $projectRepository;
        $this->projectRemover = $projectRemover;
    }

    /**
     * A project has to be removed if its channel is removed.
     *
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($channel)
    {
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        foreach ($this->projectRepository->findByChannel($channel) as $project) {
            $this->projectRemover->remove($project);
        }
    }
}
