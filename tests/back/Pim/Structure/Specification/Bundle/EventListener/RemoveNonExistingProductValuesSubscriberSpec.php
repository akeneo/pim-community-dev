<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\EventListener;

use Akeneo\Pim\Structure\Bundle\EventListener\RemoveNonExistingProductValuesSubscriber;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RemoveNonExistingProductValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance
    ) {
        $this->beConstructedWith($tokenStorage, $jobInstanceRepository, $jobLauncher, 'job_name', $createJobInstance);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveNonExistingProductValuesSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_remove_event()
    {
        $subscribedEvents = $this::getSubscribedEvents();
        $subscribedEvents->shouldHaveKey(StorageEvents::POST_REMOVE);
        $subscribedEvents[StorageEvents::POST_REMOVE]->shouldBe('launchRemoveNonExistingProductValuesJob');
    }

    function it_handles_only_attribute_option_subject(JobLauncherInterface $jobLauncher)
    {
        $entity = new \StdClass();
        $event = new GenericEvent($entity);

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->launchRemoveNonExistingProductValuesJob($event);
    }

    function it_launches_the_remove_non_existing_product_values_job(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher
    ) {
        $attribute = new Attribute();
        $attribute->setCode('color');
        $attributeOption = new AttributeOption();
        $attributeOption->setCode('blue');
        $attributeOption->setAttribute($attribute);
        $event = new GenericEvent($attributeOption);

        $user = new User();
        $token = new UsernamePasswordToken($user, 'password', 'providerKey');
        $tokenStorage->getToken()->willReturn($token);
        $jobInstance = new JobInstance();
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);

        $configuration = [
            'attribute_code' => 'color',
            'attribute_options' => ['blue'],
        ];
        $jobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalledOnce();

        $this->launchRemoveNonExistingProductValuesJob($event);
    }
}
