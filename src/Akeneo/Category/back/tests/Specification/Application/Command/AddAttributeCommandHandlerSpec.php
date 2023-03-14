<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Command\AddAttributeCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\CategorySaverProcessor;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\UpsertCategoryCommandHandler;
use Akeneo\Category\Domain\Event\AttributeAddedEvent;
use Akeneo\Category\Domain\Event\CategoryCreatedEvent;
use Akeneo\Category\Domain\Event\CategoryUpdatedEvent;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use Exception;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddAttributeCommandHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GetAttribute $getAttribute,
        CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver
    ): void {
        $this->beConstructedWith(
            $validator,
            $eventDispatcher,
            $getAttribute,
            $categoryTemplateAttributeSaver
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AddAttributeCommandHandler::class);
    }

    public function it_creates_and_saves_an_attribute(
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GetAttribute $getAttribute,
        CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver
    ): void {
        $command = AddAttributeCommand::create(
            code: 'attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'The attribute'
        );

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $templateUuid = TemplateUuid::fromString($command->templateUuid());

        $getAttribute->byTemplateUuid($templateUuid)->shouldBeCalledOnce()->willReturn(AttributeCollection::fromArray([]));

        $categoryTemplateAttributeSaver->insert($templateUuid, Argument::type(AttributeCollection::class))->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeAddedEvent::class))->shouldBeCalledOnce();

        $this->__invoke($command);
    }

    public function it_throws_an_exception_when_command_is_not_valid_on_not_blank_values(
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GetAttribute $getAttribute,
        CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver
    ): void
    {
        $command = AddAttributeCommand::create(
            code: '',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: '',
            label: ''
        );

        $validator->validate($command)->shouldBeCalled()->willReturn(
            new ConstraintViolationList([
                new ConstraintViolation('This value should not be blank.', null, [], $command, 'code', null),
                new ConstraintViolation('This value should not be blank.', null, [], $command, 'locale', null),
            ])
        );

        $templateUuid = TemplateUuid::fromString($command->templateUuid());

        $getAttribute->byTemplateUuid($templateUuid)->shouldNotBeCalled();

        $categoryTemplateAttributeSaver->insert($templateUuid, Argument::type(AttributeCollection::class))->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeAddedEvent::class))->shouldNotBeCalled();

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }

    public function it_throws_an_exception_when_command_is_not_valid_on_too_long_values(
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GetAttribute $getAttribute,
        CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver
    ): void
    {
        $command = AddAttributeCommand::create(
            code: 'attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            In consectetur magna at magna consequat lacinia. Ut dapibus nulla sit amet nibh mattis aliquet.
            In nec arcu eros. Suspendisse potenti. Etiam sagittis, diam sed commodo vehicula, libero mi mollis est.'
        );

        $validator->validate($command)->shouldBeCalled()->willReturn(
            new ConstraintViolationList([
                new ConstraintViolation('This value is too long. It should have 100 characters or less.', null, [], $command, 'code', null),
                new ConstraintViolation('This value is too long. It should have 100 characters or less.', null, [], $command, 'label', null),
            ])
        );

        $templateUuid = TemplateUuid::fromString($command->templateUuid());

        $getAttribute->byTemplateUuid($templateUuid)->shouldNotBeCalled();

        $categoryTemplateAttributeSaver->insert($templateUuid, Argument::type(AttributeCollection::class))->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeAddedEvent::class))->shouldNotBeCalled();

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }

    public function it_throws_an_exception_when_command_is_not_valid_on_wrong_format_values(
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GetAttribute $getAttribute,
        CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver
    ): void
    {
        $command = AddAttributeCommand::create(
            code: 'Attribute code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'The attribute'
        );

        $validator->validate($command)->shouldBeCalled()->willReturn(
            new ConstraintViolationList([
                new ConstraintViolation('Attribute code may contain only lowercase letters, numbers and underscores', null, [], $command, 'code', null),
            ])
        );

        $templateUuid = TemplateUuid::fromString($command->templateUuid());

        $getAttribute->byTemplateUuid($templateUuid)->shouldNotBeCalled();

        $categoryTemplateAttributeSaver->insert($templateUuid, Argument::type(AttributeCollection::class))->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeAddedEvent::class))->shouldNotBeCalled();

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$command]);
    }
}
