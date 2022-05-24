<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class ComputeFamilyVariantStructureChangesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        Connection $connection,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
            $connection,
            $logger,
            'compute_family_variant_structure_changes'
        );
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'computeVariantStructureChanges',
        ]);
    }

    function it_computes_variant_structure_changes(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        FamilyVariantInterface $familyVariant,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance,
        Connection $connection,
        Result $result,
    ) {
        $event->getArgument('is_new')->willReturn(false);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);
        $event->getSubject()->willReturn($familyVariant);
        $familyVariant->getId()->willReturn(12);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);

        $familyVariant->getCode()->willReturn('family_variant_one');

        $connection->executeQuery(Argument::cetera())->willReturn($result);
        $result->fetchAllAssociative()->willReturn([]);

        $jobLauncher->launch($jobInstance, $user, [
            'family_variant_codes' => ['family_variant_one']
        ])->shouldBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_compute_variant_structure_for_non_unitary_save(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        GenericEvent $event,
        FamilyVariantInterface $familyVariant,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $familyVariant->getId()->willReturn(150);
        $familyVariant->getCode()->willReturn('my_family_variant');
        $event->getArgument('is_new')->willReturn(false);
        $event->getSubject()->willReturn($familyVariant);

        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_it_is_a_new_family_variant(
        FamilyVariantInterface $familyVariant,
        GenericEvent $event,
        $jobLauncher
    ) {
        $event->getSubject()->willReturn($familyVariant);
        $event->getArgument('is_new')->willReturn(true);
        $familyVariant->getId()->willReturn(null);

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_it_is_not_a_family_variant(
        \stdClass $object,
        GenericEvent $event,
        $jobLauncher
    ) {
        $event->getSubject()->willReturn($object);
        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_another_job_is_already_running(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        FamilyVariantInterface $familyVariant,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance,
        Connection $connection,
        Result $result,
    ) {
        $event->getArgument('is_new')->willReturn(false);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);
        $event->getSubject()->willReturn($familyVariant);
        $familyVariant->getId()->willReturn(12);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);

        $familyVariant->getCode()->willReturn('family_variant_one');

        $connection->executeQuery(Argument::cetera())->willReturn($result);
        $result->fetchAllAssociative()->willReturn([['id' => 'job_id_already_started']]);

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }
}
