<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Api\Event\CategoryCreatedEvent;
use Akeneo\Category\Api\Event\CategoryUpdatedEvent;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Query\FindCategoryByCode;
use Akeneo\Category\Application\Storage\ProcessCategoryUpdateMock;
use Akeneo\Category\Application\UpsertCategoryCommandHandler;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Exception;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandHandlerSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        FindCategoryByCode $findCategoryByCode,
        UserIntentApplierRegistry $applierRegistry,
        EventDispatcherInterface $eventDispatcher,
        ProcessCategoryUpdateMock $updater
    ) {
        $this->beConstructedWith(
            $validator,
            $findCategoryByCode,
            $applierRegistry,
            $eventDispatcher,
            $updater
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpsertCategoryCommandHandler::class);
    }

    function it_updates_and_saves_a_category(
        FindCategoryByCode $findCategoryByCode,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        ProcessCategoryUpdateMock $updater
    ) {
        $command = new UpsertCategoryCommand('code');
        $category = new Category(
            new CategoryId(1),
            new Code('code'),
            LabelCollection::fromArray([]),
            null
        );
        $event = new CategoryUpdatedEvent('code');
        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $findCategoryByCode->__invoke('code')->shouldBeCalledOnce()->willReturn($category);
        $updater->update($category, $command->userIntents())->shouldBeCalledOnce();
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

        $this->__invoke($command);
    }

    function it_creates_and_save_a_category(
        FindCategoryByCode $findCategoryByCode,
        ValidatorInterface $validator,
        ProcessCategoryUpdateMock $updater
    ) {
        $command = new UpsertCategoryCommand('code');
        $category = new Category(
            new CategoryId(1),
            new Code('code'),
            LabelCollection::fromArray([]),
            null
        );
        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $findCategoryByCode->__invoke('code')->shouldBeCalledOnce()->willReturn(null);
        $updater->update($category, $command->userIntents())->shouldNotBeCalled();

        $this->shouldThrow(new Exception("Command to create a category is in progress."))->during__invoke($command);
    }

    function it_throw_an_exception_when_command_is_not_valid(
        ValidatorInterface $validator,
        FindCategoryByCode $findCategoryByCode,
        UserIntentApplierRegistry $applierRegistry,
        EventDispatcherInterface $eventDispatcher,
        ProcessCategoryUpdateMock $updater
    ) {
        $command = new UpsertCategoryCommand('');
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn($violations);
        $findCategoryByCode->__invoke('')->shouldNotBeCalled();
        $applierRegistry->getApplier(Argument::type(UserIntent::class))->shouldNotBeCalled();
        $updater->update(Argument::type(Category::class), Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(CategoryUpdatedEvent::class))->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(CategoryCreatedEvent::class))->shouldNotBeCalled();

        $this->shouldThrow(new ViolationsException($violations))->during__invoke($command);
    }

    function it_throws_an_exception_when_updater_throws_an_exception(
        FindCategoryByCode $findCategoryByCode,
        UserIntentApplierRegistry $applierRegistry,
        UserIntentApplier $userIntentApplier,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        ProcessCategoryUpdateMock $updater
    ) {
        $setLabelUserIntent = new SetLabel('en_US', 'The label');
        $command = new UpsertCategoryCommand('code', [$setLabelUserIntent]);
        $category = new Category(
            new CategoryId(1),
            new Code('code'),
            LabelCollection::fromArray([]),
            null
        );
        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $findCategoryByCode->__invoke('code')->shouldBeCalledOnce()->willReturn($category);
        $applierRegistry->getApplier($setLabelUserIntent)->willReturn($userIntentApplier);
        $userIntentApplier->apply($setLabelUserIntent, $category)->willThrow(InvalidArgumentException::class);

        $updater->update($category, $command->userIntents())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(CategoryUpdatedEvent::class))->shouldNotBeCalled();
        $this->shouldThrow(ViolationsException::class)->during__invoke($command);
    }
}
