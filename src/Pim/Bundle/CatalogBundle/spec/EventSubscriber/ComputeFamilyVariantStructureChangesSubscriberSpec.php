<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class ComputeFamilyVariantStructureChangesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->beConstructedWith(
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
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
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($familyVariant);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_family_variant_structure_changes')
            ->willReturn($jobInstance);

        $familyVariant->getCode()->willReturn('family_variant_one');

        $jobLauncher->launch($jobInstance, $user, [
            'family_variant_codes' => ['family_variant_one']
        ])->shouldBeCalled();

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
}
