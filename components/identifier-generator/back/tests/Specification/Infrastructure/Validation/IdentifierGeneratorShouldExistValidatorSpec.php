<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorShouldExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(IdentifierGeneratorRepository $identifierGeneratorRepository, ExecutionContext $context): void
    {
        $this->beConstructedWith($identifierGeneratorRepository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IdentifierGeneratorShouldExistValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_update_generator_command(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ): void {
        $identifierGeneratorRepository
            ->get((string)Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \stdClass(), new IdentifierGeneratorShouldExist());
    }

    public function it_should_build_violation_when_code_attribute_does_not_exist(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ExecutionContext $context,
    ): void {
        $context->buildViolation(
            'validation.update.identifier_generator_code_not_found',
            ['{{code}}' => 'non_existing_generator']
        )->shouldBeCalled();

        $identifierGeneratorRepository
            ->get('non_existing_generator')
            ->shouldBeCalledOnce()
            ->willThrow(new CouldNotFindIdentifierGeneratorException('non_existing_generator'));

        $updateGeneratorCommand = new UpdateGeneratorCommand(
            'non_existing_generator',
            [],
            [['type' => 'unknown', 'string' => 'abcdef']],
            ['fr' => 'Générateur'],
            'sku',
            '-',
            'no',
        );
        $this->validate($updateGeneratorCommand, new IdentifierGeneratorShouldExist());
    }

    public function it_should_be_valid_when_code_attribute_exist(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ExecutionContext $context
    ): void {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('mygenerator'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
        $identifierGeneratorRepository
            ->get('mygenerator')
            ->shouldBeCalledOnce()
            ->willReturn($identifierGenerator);

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $updateGeneratorCommand = new UpdateGeneratorCommand(
            'mygenerator',
            [],
            [['type' => 'unknown', 'string' => 'abcdef']],
            ['fr' => 'Générateur'],
            'sku',
            '-',
            'no',
        );
        $this->validate($updateGeneratorCommand, new IdentifierGeneratorShouldExist());
    }
}
