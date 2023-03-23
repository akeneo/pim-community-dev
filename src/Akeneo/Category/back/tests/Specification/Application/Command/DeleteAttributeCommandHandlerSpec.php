<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Command\DeleteAttributeCommand;
use Akeneo\Category\Application\Command\DeleteAttributeCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\Query\DeleteTemplateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteAttributeCommandHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        DeactivateAttribute $deactivateAttribute
    ): void {
        $this->beConstructedWith(
            $validator,
            $deactivateAttribute,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DeleteAttributeCommandHandler::class);
    }

    public function it_creates_and_delete_an_attribute(
        ValidatorInterface $validator,
        DeactivateAttribute $deactivateAttribute,
    ): void {
        $command = DeleteAttributeCommand::create(
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            attributeUuid: '1417f013-c060-45b3-9bd5-2adaee07170f'
        );

        $validator->validate($command)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);

        $deactivateAttribute->execute($templateUuid, $attributeUuid)->shouldBeCalledOnce();

        $this->__invoke($command);
    }
}
