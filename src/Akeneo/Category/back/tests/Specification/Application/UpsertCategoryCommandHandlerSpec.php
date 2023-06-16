<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application;

use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Storage\Save\CategorySaverProcessor;
use Akeneo\Category\Application\UpsertCategoryCommandHandler;
use Akeneo\Category\Domain\Event\CategoryUpdatedEvent;
use Akeneo\Category\Domain\Exceptions\ViolationsException;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
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
        GetCategoryInterface $getCategory,
        UserIntentApplierRegistry $applierRegistry,
        EventDispatcherInterface $eventDispatcher,
        CategorySaverProcessor $saver,
        FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry
    ) {
        $this->beConstructedWith(
            $validator,
            $getCategory,
            $applierRegistry,
            $eventDispatcher,
            $saver,
            $findCategoryAdditionalPropertiesRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpsertCategoryCommandHandler::class);
    }

    function it_creates_and_saves_a_category(
        GetCategoryInterface $getCategory,
        ValidatorInterface $validator,
        CategorySaverProcessor $saver,
        FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry
    ) {
        $command = new UpsertCategoryCommand('code');
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null
        );
        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $getCategory->byCode('code')->shouldBeCalledOnce()->willReturn(null);
        $findCategoryAdditionalPropertiesRegistry->forCategory(Argument::type(Category::class))->shouldNotBeCalled();
        $saver->save($category, $command->userIntents())->shouldNotBeCalled();

        $this->shouldThrow(new Exception("Command to create a category is in progress."))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_command_is_not_valid(
        ValidatorInterface $validator,
        GetCategoryInterface $getCategory,
        UserIntentApplierRegistry $applierRegistry,
        EventDispatcherInterface $eventDispatcher,
        CategorySaverProcessor $saver,
        FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry
    ) {
        $command = new UpsertCategoryCommand('');
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);

        $validator->validate($command)->shouldBeCalledOnce()->willReturn($violations);
        $getCategory->byCode('')->shouldNotBeCalled();
        $findCategoryAdditionalPropertiesRegistry->forCategory(Argument::type(Category::class))->shouldNotBeCalled();
        $applierRegistry->getApplier(Argument::type(UserIntent::class))->shouldNotBeCalled();
        $saver->save(Argument::type(Category::class), Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(CategoryUpdatedEvent::class))->shouldNotBeCalled();

        $this->shouldThrow(new ViolationsException($violations))->during('__invoke', [$command]);
    }

    function it_throws_an_exception_when_updater_throws_an_exception(
        GetCategoryInterface $getCategory,
        UserIntentApplierRegistry $applierRegistry,
        UserIntentApplier $userIntentApplier,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        CategorySaverProcessor $saver,
        FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry
    ) {
        $setLabelUserIntent = new SetLabel('en_US', 'The label');
        $command = new UpsertCategoryCommand('code', [$setLabelUserIntent]);
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null
        );
        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $getCategory->byCode('code')->shouldBeCalledOnce()->willReturn($category);
        $findCategoryAdditionalPropertiesRegistry->forCategory($category)->shouldBeCalledOnce()->willReturn($category);
        $applierRegistry->getApplier($setLabelUserIntent)->willReturn($userIntentApplier);
        $userIntentApplier->apply($setLabelUserIntent, $category)->willThrow(InvalidArgumentException::class);

        $saver->save($category, $command->userIntents())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(CategoryUpdatedEvent::class))->shouldNotBeCalled();
        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }
}
