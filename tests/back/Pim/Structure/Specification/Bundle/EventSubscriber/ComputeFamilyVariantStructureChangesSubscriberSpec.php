<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
            StorageEvents::PRE_SAVE => 'recordIsNewFamilyVariant',
            StorageEvents::POST_SAVE => 'computeVariantStructureChanges',
            StorageEvents::POST_SAVE_ALL => 'bulkComputeVariantStructureChanges',
        ]);
    }

    function it_computes_variant_structure_changes(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        FamilyVariantInterface $familyVariant,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance,
        Connection $connection,
        Result $result,
    ) {
        $event = new GenericEvent($familyVariant->getWrappedObject(), ['is_new' => false, 'unitary' => true]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);
        $jobInstance->getId()->willReturn(124);

        $familyVariant->getId()->willReturn(12);
        $familyVariant->getCode()->willReturn('family_variant_one');
        $familyVariant->releaseEvents()->willReturn([FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL]);

        $connection->executeQuery(Argument::cetera())->willReturn($result);
        $result->fetchOne()->willReturn(false);

        $jobLauncher->launch($jobInstance, $user, [
            'family_variant_codes' => ['family_variant_one']
        ])->shouldBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_compute_variant_structure_for_non_unitary_save(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        FamilyVariantInterface $familyVariant,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $familyVariant->getId()->willReturn(150);
        $familyVariant->getCode()->willReturn('my_family_variant');
        $event = new GenericEvent($familyVariant->getWrappedObject(), ['is_new' => false, 'unitary' => false]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_compute_variant_structure_when_job_launching_is_disabled(
        SimpleJobLauncher $jobLauncher,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getId()->willReturn(150);
        $familyVariant->getCode()->willReturn('my_family_variant');
        $event = new GenericEvent($familyVariant->getWrappedObject(), [
            'is_new' => false,
            'unitary' => true,
            ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => true,
        ]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_it_is_a_new_family_variant(
        FamilyVariantInterface $familyVariant,
        SimpleJobLauncher $jobLauncher
    ) {
        $event = new GenericEvent($familyVariant->getWrappedObject(), ['is_new' => true, 'unitary' => true]);
        $familyVariant->getId()->willReturn(null);

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_it_is_not_a_family_variant(
        \stdClass $object,
        GenericEvent $event,
        SimpleJobLauncher $jobLauncher
    ) {
        $event->getSubject()->willReturn($object);
        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_does_not_launch_a_job_if_another_job_is_already_running(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        FamilyVariantInterface $familyVariant,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance,
        Connection $connection,
        Result $result,
    ) {
        $event = new GenericEvent($familyVariant->getWrappedObject(), ['is_new' => false, 'unitary' => true]);
        $familyVariant->getId()->willReturn(12);
        $familyVariant->releaseEvents()->willReturn([FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);
        $jobInstance->getId()->willReturn(124);

        $familyVariant->getCode()->willReturn('family_variant_one');

        $connection->executeQuery(Argument::cetera())->willReturn($result);
        $result->fetchOne()->willReturn(4000);

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->computeVariantStructureChanges($event);
    }

    function it_computes_variant_structure_changes_for_a_batch_of_family_variants(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        FamilyVariantInterface $familyVariant1,
        FamilyVariantInterface $familyVariant2,
        FamilyVariantInterface $newFamilyVariant,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance,
        Connection $connection,
        Result $result,
        Result $result2,
        Result $result3
    ) {
        $event = new GenericEvent([
            $familyVariant1->getWrappedObject(),
            $familyVariant2->getWrappedObject(),
            $newFamilyVariant->getWrappedObject(),
        ]);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);
        $jobInstance->getId()->willReturn(124);

        $familyVariant1->getId()->willReturn(12);
        $familyVariant1->getCode()->willReturn('family_variant_one');
        $familyVariant1->releaseEvents()->willReturn([FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL]);
        $familyVariant2->getId()->willReturn(13);
        $familyVariant2->getCode()->willReturn('family_variant_two');
        $familyVariant2->releaseEvents()->willReturn([FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL]);
        $newFamilyVariant->getId()->willReturn(null);
        $newFamilyVariant->getCode()->willReturn('new_family_variant');
        $newFamilyVariant->releaseEvents()->willReturn([FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL]);

        $connection->executeQuery(Argument::any(), ['instanceId' => 124, 'familyVariantCode' => 'family_variant_one'])
            ->willReturn($result);
        $result->fetchOne()->willReturn(4000);
        $connection->executeQuery(Argument::any(), ['instanceId' => 124, 'familyVariantCode' => 'family_variant_two'])
            ->willReturn($result2);
        $result2->fetchOne()->willReturn(false);
        $connection->executeQuery(Argument::any(), ['instanceId' => 124, 'familyVariantCode' => 'new_family_variant'])
            ->willReturn($result3);
        $result3->fetchOne()->willReturn(false);

        $jobLauncher->launch($jobInstance, $user, [
            'family_variant_codes' => ['family_variant_two']
        ])->shouldBeCalledOnce();

        $this->recordIsNewFamilyVariant(new GenericEvent($newFamilyVariant->getWrappedObject()));
        $this->bulkComputeVariantStructureChanges($event);
    }

    function it_does_not_compute_bulk_variant_structure_when_job_launching_is_disabled(
        SimpleJobLauncher $jobLauncher,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getId()->willReturn(150);
        $familyVariant->getCode()->willReturn('my_family_variant');
        $event = new GenericEvent(
            [$familyVariant],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => true]
        );

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->bulkComputeVariantStructureChanges($event);
    }
}
