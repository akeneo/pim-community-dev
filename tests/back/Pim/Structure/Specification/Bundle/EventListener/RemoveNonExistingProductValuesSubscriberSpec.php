<?php


declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Structure\Bundle\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Bundle\EventListener\RemoveNonExistingProductValuesSubscriber;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RemoveNonExistingProductValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher
    ) {
        $this->beConstructedWith($tokenStorage, $jobInstanceRepository, $jobLauncher, 'job_name');
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

    function it_launches_the_job_with_filters(
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
            'filters' => [
                [
                    'field' => 'color',
                    'operator' => Operators::IN_LIST,
                    'value' => ['blue'],
                    'context' => ['ignore_non_existing_values' => true],
                ],
                [
                    'field' => 'attributes_for_this_level',
                    'operator' => Operators::IN_LIST,
                    'value' => ['color'],
                ],
            ],
        ];
        $jobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalledOnce();

        $this->launchRemoveNonExistingProductValuesJob($event);
    }
}
