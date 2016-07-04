<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class OperationJobLauncherSpec extends ObjectBehavior
{
    function let(
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepo,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($jobLauncher, $jobInstanceRepo, $tokenStorage);
    }

    function it_launches_a_background_process_from_an_operation(
        $jobLauncher,
        $jobInstanceRepo,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        AbstractMassEditOperation $operation,
        JobInstance $jobInstance
    ) {
        $operation->getJobInstanceCode()->willReturn('mass_classify');
        $jobInstanceRepo->findOneBy(['code' => 'mass_classify'])->willReturn($jobInstance);

        $operation->finalize()->shouldBeCalled();
        $operation->getBatchConfig()->willReturn([
            'foo'  => 'bar',
            'pomf' => 'thud'
        ]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'foo'  => 'bar',
                'pomf' => 'thud'
            ]
        );

        $this->launch($operation);
    }

    function it_throws_an_exception_if_no_job_instance_is_found(
        $jobInstanceRepo,
        AbstractMassEditOperation $operation
    ) {
        $operation->getJobInstanceCode()->willReturn('mass_colorize');
        $jobInstanceRepo->findOneBy(['code' => 'mass_colorize'])->willReturn(null);

        $this->shouldThrow(new NotFoundResourceException(
            'No JobInstance found with code "mass_colorize"'
        ))->during('launch', [$operation]);
    }
}
