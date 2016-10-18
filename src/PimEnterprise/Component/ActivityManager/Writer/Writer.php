<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Writer;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Writer implements ItemWriterInterface
{
    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ObjectDetacher */
    private $objectDetacher;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     * @param EntityManagerInterface     $entityManager
     * @param ObjectDetacherInterface    $objectDetacher
     */
    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        EntityManagerInterface $entityManager,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->projectRepository = $projectRepository;
        $this->entityManager = $entityManager;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $project = $this->findProject(1);
        foreach ($items as $item) {
            foreach ($item as $userGroup) {
                $project->addUserGroup($userGroup);
            }
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $this->objectDetacher->detach($project);
    }

    /**
     * @param string $code
     *
     * @return Project
     */
    private function findProject($code)
    {
        $project = $this->projectRepository->findOneById(1);

        if (null === $project) {
            throw new NotFoundResourceException(sprintf('Could not found any project with code "%s"', $code));
        }

        return $project;
    }
}
