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
use Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Doctrine\ORM\EntityManager;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Writer implements ItemWriterInterface
{
    private $projectRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ObjectDetacher */
    private $objectDetacher;

    /**
     * @param ProjectRepository $projectRepository
     * @param EntityManager     $entityManager
     * @param ObjectDetacher    $objectDetacher
     */
    public function __construct(
        ProjectRepository $projectRepository,
        EntityManager $entityManager,
        ObjectDetacher $objectDetacher
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
        $project = $this->findProject();
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
     * @return Project
     */
    private function findProject()
    {
        $project = $this->projectRepository->findOneBy(['code' => 'toto']);
        if (null === $project) {

        }

        return $project;
    }
}
