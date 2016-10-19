<?php

namespace spec\Akeneo\ActivityManager\Component\Writer;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Writer\Writer;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;

class WriterSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        EntityManagerInterface $entityManager,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $projectRepository,
            $entityManager,
            $objectDetacher
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Writer::class);
    }

    function it_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_a_writes_a_project($objectDetacher, $entityManager, $stepExecution, $projectRepository, ProjectInterface $project, JobParameters $jobParameters, Group $userGroup)
    {
        $items = [
            [
                $userGroup,
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('project_id')->willReturn(1);

        $projectRepository->find(1)->willReturn($project);

        $project->addUserGroup($userGroup)->shouldBeCalled();
        $entityManager->persist($project)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $objectDetacher->detach($project)->shouldBeCalled();

        $this->write($items);
    }
}
