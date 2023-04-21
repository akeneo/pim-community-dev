<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Infrastructure\Processor\Denormalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Updater\ChannelUpdater;
use Akeneo\Channel\Infrastructure\Processor\Denormalization\ChannelProcessor;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelProcessorSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactory $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ): void {
        $this->beConstructedWith($repository, $factory, $updater, $validator, $objectDetacher);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ChannelProcessor::class);
    }

    public function it_is_a_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_updates_an_existing_channel(
        IdentifiableObjectRepositoryInterface $repository,
        ChannelUpdater $updater,
        ValidatorInterface $validator,
        ChannelInterface $channel,
    ): void {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn(new ConstraintViolationList());

        $this
            ->process($values)
            ->shouldReturn($channel);
    }

    public function it_skips_a_channel_when_update_fails(
        IdentifiableObjectRepositoryInterface $repository,
        ChannelUpdater $updater,
        ValidatorInterface $validator,
        ChannelInterface $channel,
    ): void {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn(new ConstraintViolationList());

        $this
            ->process($values)
            ->shouldReturn($channel);

        $updater
            ->update($channel, $values)
            ->willThrow(new InvalidPropertyException('code', 'value', 'className', 'The code could not be blank.'));

        $this
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$values]
            );
    }

    public function it_skips_a_channel_when_object_is_invalid(
        IdentifiableObjectRepositoryInterface $repository,
        ChannelUpdater $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        ChannelInterface $channel
    ): void {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($channel)
            ->willReturn($violations);

        $objectDetacher->detach($channel)->shouldBeCalled();
        $this
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$values]
            );
    }

    public function it_does_not_create_the_same_channel_twice_in_the_same_batch(
        IdentifiableObjectRepositoryInterface $repository,
        ChannelUpdater $updater,
        ValidatorInterface $validator,
        SimpleFactoryInterface $factory,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        ChannelInterface $channel
    ): void {
        $this->setStepExecution($stepExecution);
        $repository->getIdentifierProperties()->willReturn(['code']);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get('processed_items_batch')->willReturn(null);

        $repository->findOneByIdentifier('mycode')->willReturn(null);
        $factory->create()->shouldBeCalledTimes(1)->willReturn($channel);

        $executionContext
            ->put('processed_items_batch', ['mycode' => $channel])
            ->shouldBeCalled()
            ->will(function() use ($executionContext, $channel) {
                $executionContext->get('processed_items_batch')->willReturn(['mycode' => $channel]);
            });

        $firstChannelValues = $this->getValues();

        $updater
            ->update($channel, $firstChannelValues)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn(new ConstraintViolationList());

        $this
            ->process($firstChannelValues)
            ->shouldReturn($channel);

        $secondChannelValues = $this->getValues();
        $secondChannelValues['label'] = 'Another label';

        $updater
            ->update($channel, $secondChannelValues)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn(new ConstraintViolationList());

        $this
            ->process($secondChannelValues)
            ->shouldReturn($channel);
    }

    public function it_remove_relationship_between_locale_and_channel_on_validation_error_for_new_channel(
        SimpleFactoryInterface $factory,
        IdentifiableObjectRepositoryInterface $repository,
        ChannelUpdater $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        ChannelInterface $channel,
        LocaleInterface $locale
    ): void {
        $factory->create()
            ->shouldBeCalledTimes(1)
            ->willReturn($channel);
        $channel
            ->getId()
            ->shouldBeCalled()
            ->willReturn(null);
        $channel
            ->getLocales()
            ->shouldBeCalled()
            ->willReturn([$locale]);
        $channel
            ->removeLocale(Argument::any())
            ->shouldBeCalledOnce();

        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn(null);

        $values = $this->getValues();

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($channel)
            ->shouldBeCalled()
            ->willReturn($violations);

        $objectDetacher->detach($channel)->shouldBeCalled();

        $this
            ->shouldThrow(InvalidItemFromViolationsException::class)
            ->during(
                'process',
                [$values]
            );
    }

    private function getValues(): array
    {
        return [
            'code'       => 'mycode',
            'label'      => 'Ecommerce',
            'locales'    => ['en_US', 'fr_FR'],
            'currencies' => ['EUR', 'USD'],
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];
    }
}
