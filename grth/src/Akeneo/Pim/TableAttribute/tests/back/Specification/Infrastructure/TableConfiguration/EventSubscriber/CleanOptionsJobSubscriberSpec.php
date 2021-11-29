<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber\CleanOptionsJobSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanOptionsJobSubscriberSpec extends ObjectBehavior
{
    function let(
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        CreateJobInstanceInterface $createJobInstance
    ) {
        $this->beConstructedWith(
            $tokenStorage,
            $jobInstanceRepository,
            $jobLauncher,
            $createJobInstance,
            'clean_table_values_following_deleted_options'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CleanOptionsJobSubscriber::class);
    }

    function it_does_nothing_if_subject_is_not_an_attribute(
        GenericEvent $event,
        TokenStorageInterface $tokenStorage
    ) {
        $selectOptionWasDeleted = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('salt'));
        $this->aSelectOptionWasDeleted($selectOptionWasDeleted);

        $tokenStorage->getToken()->shouldNotBeCalled();

        $event->getSubject()->shouldBeCalled()->willReturn(new \stdClass());
        $this->createCleanOptionsJobIfNeeded($event);
    }

    function it_does_nothing_if_there_was_no_deletion_event(
        TokenStorageInterface $tokenStorage,
        AttributeInterface $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);

        $tokenStorage->getToken()->shouldNotBeCalled();

        $this->createCleanOptionsJobIfNeeded($event);
    }

    function it_launches_a_job_with_several_columns(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        AttributeInterface $attribute,
        JobInstance $jobInstance
    ) {
        $selectOptionWasDeleted1 = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('salt'));
        $selectOptionWasDeleted2 = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('sugar'));
        $selectOptionWasDeleted3 = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('nutrition_score'), SelectOptionCode::fromString('B'));
        $otherAttributeSelectOptionWasDeleted = new SelectOptionWasDeleted('other', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('egg'));
        $this->aSelectOptionWasDeleted($selectOptionWasDeleted1);
        $this->aSelectOptionWasDeleted($selectOptionWasDeleted2);
        $this->aSelectOptionWasDeleted($selectOptionWasDeleted3);
        $this->aSelectOptionWasDeleted($otherAttributeSelectOptionWasDeleted);

        $tokenStorage->getToken()->shouldBeCalled()->willReturn($token);
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('clean_table_values_following_deleted_options')->shouldBeCalled()->willReturn($jobInstance);
        $jobLauncher->launch($jobInstance, $user, [
            'attribute_code' => 'nutrition',
            'removed_options_per_column_code' => [
                'ingredients' => ['salt', 'sugar'],
                'nutrition_score' => ['B'],
            ]
        ])->shouldBeCalled();

        $event->getSubject()->shouldBeCalled()->willReturn($attribute);
        $attribute->getCode()->shouldBeCalled()->willReturn('nutrition');
        $this->createCleanOptionsJobIfNeeded($event);
    }

    function it_creates_job_instance_and_launches_a_job(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        AttributeInterface $attribute,
        JobInstance $jobInstance
    ) {
        $selectOptionWasDeleted = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('salt'));
        $this->aSelectOptionWasDeleted($selectOptionWasDeleted);

        $tokenStorage->getToken()->shouldBeCalled()->willReturn($token);
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('clean_table_values_following_deleted_options')->shouldBeCalled()
            ->willReturn(null, $jobInstance);
        $createJobInstance->createJobInstance([
            'code' => 'clean_table_values_following_deleted_options',
            'label' => 'Remove the non existing table option values from product and product models',
            'job_name' => 'clean_table_values_following_deleted_options',
            'type' => 'clean_table_values_following_deleted_options',
        ])->shouldBeCalled();
        $jobLauncher->launch($jobInstance, $user, [
            'attribute_code' => 'nutrition',
            'removed_options_per_column_code' => [
                'ingredients' => ['salt'],
            ]
        ])->shouldBeCalled();

        $event->getSubject()->shouldBeCalled()->willReturn($attribute);
        $attribute->getCode()->shouldBeCalled()->willReturn('nutrition');
        $this->createCleanOptionsJobIfNeeded($event);
    }
}
