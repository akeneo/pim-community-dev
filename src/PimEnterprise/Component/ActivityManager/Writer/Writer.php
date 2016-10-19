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

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Writer implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

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
        $parameters = $this->stepExecution->getJobParameters();
        $projectId = $parameters->get('project_id');

        $project = $this->findProject($projectId);
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
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param int $id
     *
     * @return ProjectInterface
     * @throws EntityNotFoundException
     */
    private function findProject($id)
    {
        $project = $this->projectRepository->find($id);

        if (null === $project) {
            throw new EntityNotFoundException(sprintf('Could not found any project with id "%s"', $id));
        }

        return $project;
    }
}
