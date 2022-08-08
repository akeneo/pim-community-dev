<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application;

use Akeneo\Category\Api\Command\Event\CategoryUpdatedEvent;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Query\FindCategoryByCode;
use Akeneo\Category\Application\UpsertCategoryCommandHandler;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Exception;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandHandlerSpec extends ObjectBehavior
{
    function let(
        FindCategoryByCode $findCategoryByCode,
        UserIntentApplierRegistry $applierRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $findCategoryByCode,
            $applierRegistry,
            $eventDispatcher
        );

    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpsertCategoryCommandHandler::class);
    }

    function it_updates_and_saves_a_category(
        FindCategoryByCode $findCategoryByCode,
        EventDispatcherInterface $eventDispatcher
    ) {
        $command = new UpsertCategoryCommand('code');
        $category = new Category(
            new CategoryId(1),
            new Code('code'),
            LabelCollection::fromArray([]),
            null
        );
        $event = new CategoryUpdatedEvent('code');

        $findCategoryByCode->__invoke('code')->shouldBeCalledOnce()->willReturn($category);
        $eventDispatcher->dispatch($event)->shouldBeCalledOnce()->willReturn($event);

        $this->__invoke($command);
    }

    function it_creates_and_save_a_category(FindCategoryByCode $findCategoryByCode)
    {
        $command = new UpsertCategoryCommand('code');
        $findCategoryByCode->__invoke('code')->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(Exception::class)->during('__invoke', [$command]);
    }

//    function it_throws_an_exception_when_updater_throws_an_exception()
//    {
//
//    }

}
