<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\CompletenessHasBeenUpdated;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ComputeCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(
        Connection $connection,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        CreateJobInstanceInterface $createJobInstance,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->beConstructedWith(
        $connection,
        $jobLauncher,
        $jobInstanceRepository,
        $createJobInstance,
        $tokenStorage,
        'compute_completeness_following_table_update'
        );
    }

    function it_does_nothing_if_subject_is_not_an_attribute(
        GenericEvent $event,
        TokenStorageInterface $tokenStorage
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn(new \stdClass());
        $tokenStorage->getToken()->shouldNotBeCalled();

        $this->launchComputeCompletenessJobIfNeeded($event);
    }

    function it_does_nothing_if_subject_is_not_a_table_attribute(
        GenericEvent $event,
        TokenStorageInterface $tokenStorage,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);
        $tokenStorage->getToken()->shouldNotBeCalled();

        $this->launchComputeCompletenessJobIfNeeded($event);
    }

    function id_does_nothing_if_attribute_is_not_required_for_completeness_in_related_families(
        GenericEvent $event,
        Connection $connection,
        TokenStorageInterface $tokenStorage,
        AttributeInterface $attribute,
        Result $result
    ) {
        $attribute->getType()->WillReturn(AttributeTypes::TABLE);
        $attribute->getCode()->WillReturn('table_attribute_code');
        $event->getSubject()->willReturn($attribute);

        $connection->executeQuery(Argument::cetera(), ['attribute_code' => 'table_attribute_code'])
            ->shouldBeCalledOnce()
            ->willReturn($result);
        $result->fetchFirstColumn()->willReturn([]);

        $tokenStorage->getToken()->shouldNotBeCalled();

        $this->launchComputeCompletenessJobIfNeeded($event);
    }

    function it_computes_completeness_when_attribute_is_related_to_family(
        AttributeInterface $attribute,
        Connection $connection,
        GenericEvent $event,
        JobInstance $jobInstance,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        Result $result,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    ) {
        $attribute->getType()->WillReturn(AttributeTypes::TABLE);
        $attribute->getCode()->WillReturn('table_attribute_code');
        $attribute->getRawTableConfiguration()->willReturn([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'code' => 'ingredient',
                'data_type' => 'select',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => true,
            ],
            [
                'id' => ColumnIdGenerator::description(),
                'code' => 'description',
                'data_type' => 'text',
                'labels' => (object) [],
                'validations' => (object) [],
            ],
        ]);
        $event->getSubject()->willReturn($attribute);

        $connection->executeQuery(Argument::cetera(), ['attribute_code' => 'table_attribute_code'])
            ->shouldBeCalledOnce()
            ->willReturn($result);
        $result->fetchFirstColumn()->willReturn(['family_code_1']);

        $tokenStorage->getToken()->shouldBeCalledOnce()->willReturn($token);
        $token->getUser()->shouldBeCalledOnce()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('compute_completeness_following_table_update')
            ->shouldBeCalledOnce()
            ->willReturn($jobInstance);
        $jobLauncher->launch($jobInstance, $user, [
            'attribute_code' => 'table_attribute_code',
            'family_codes' => ['family_code_1'],
        ])->shouldBeCalledOnce();

        $this->completenessHasBeenUpdated(new CompletenessHasBeenUpdated('table_attribute_code'));
        $this->launchComputeCompletenessJobIfNeeded($event);
    }
}
