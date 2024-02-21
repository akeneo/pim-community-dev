<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\MassEditAction;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class OperationJobLauncherSpec extends ObjectBehavior
{
    public function let(
        SimpleJobLauncher $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobLauncher, $jobInstanceRepo, $tokenStorage);
    }

    public function it_launches_a_background_process_from_an_operation(
        $jobLauncher,
        $jobInstanceRepo,
        $tokenStorage,
        TokenInterface $token,
        BatchableOperationInterface $operation,
        JobInstance $jobInstance
    ): void {
        $user = new User();
        $user->setUsername('julia');

        $operation->getJobInstanceCode()->willReturn('mass_classify');
        $jobInstanceRepo->findOneByIdentifier('mass_classify')->willReturn($jobInstance);

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
                'pomf' => 'thud',
                'users_to_notify' => ['julia']
            ]
        );

        $this->launch($operation);
    }

    public function it_throws_an_exception_if_no_job_instance_is_found(
        $jobInstanceRepo,
        BatchableOperationInterface $operation
    ): void {
        $operation->getJobInstanceCode()->willReturn('mass_colorize');
        $jobInstanceRepo->findOneByIdentifier('mass_colorize')->willReturn(null);

        $this->shouldThrow(new NotFoundResourceException(
            'No JobInstance found with code "mass_colorize"'
        ))->during('launch', [$operation]);
    }
}
