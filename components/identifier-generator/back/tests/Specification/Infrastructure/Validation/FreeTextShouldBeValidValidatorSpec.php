<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FreeTextShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'free_text', 'string' => 'abcdef'], new NotBlank()]);
    }

//    public function it_can_only_validate_array_argument(ExecutionContext $context, FreeTextShouldBeValid $constraint): void
//    {
//        $this->shouldThrow(\InvalidArgumentException::class)
//            ->during('validate', [['type' => 'free_text', 'string' => 'abcdef'], new FreeTextShouldBeValid()]);
//
//        $context
//            ->buildViolation(Argument::cetera())
//            ->shouldNotBeCalled();
//        $this->validate(new \stdClass(), $constraint);
//    }

//    public function it_should_throw_an_error_when_a_property_is_not_an_array(ExecutionContext $context)
//    {
//        $command = new CreateGeneratorCommand(
//            'generatorCode',
//            [],
//            ['type' => 'free_text', 'string' => 'abcdef'],
//            ['fr' => 'Générateur'],
//            'sku',
//            '-'
//        );
//        $context->getRoot()
//            ->shouldBeCalled()
//            ->willReturn($command);
//
//        $this->shouldThrow(new \InvalidArgumentException('Expected an array. Got: stdClass'))
//            ->during('validate', [new \stdClass(), new FreeTextShouldBeValid()]);
//    }
//
//    public function it_should_throw_an_error_when_a_property_does_not_contain_valid_keys(ExecutionContext $context)
//    {
//        $command = new CreateGeneratorCommand(
//            'generatorCode',
//            [],
//            ['type' => 'free_text', 'string' => 'abcdef'],
//            ['fr' => 'Générateur'],
//            'sku',
//            '-'
//        );
//        $context->getRoot()
//            ->shouldBeCalled()
//            ->willReturn($command);
//
//        $this->shouldThrow(new \InvalidArgumentException('Expected the key "type" to exist.'))
//            ->during('validate', [['string' => 'abcdef'], new FreeTextShouldBeValid()]);
//        $this->shouldThrow(new \InvalidArgumentException('Expected the key "string" to exist.'))
//            ->during('validate', [['type' => 'free_text'], new FreeTextShouldBeValid()]);
//    }
//
//    public function it_should_build_violation_when_free_text_length_is_reached(ExecutionContext $context): void
//    {
//        $freeText = [
//            'type' => 'free_text',
//            'string' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ' .
//                'ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna ' .
//                'lorem.  Praesent non lectus sit amet lectus sollicitudin consectetur sed non.',
//        ];
//
//        $command = new CreateGeneratorCommand(
//            'generatorCode',
//            [],
//            ['type' => 'free_text', 'string' => 'abcdef'],
//            ['fr' => 'Générateur'],
//            'sku',
//            '-'
//        );
//        $context->getRoot()
//            ->shouldBeCalled()
//            ->willReturn($command);
//
//        $context->buildViolation(
//            'validation.create.free_text_size_limit_reached',
//            ['{{limit}}' => 100]
//        )->shouldBeCalled();
//
//        $this->validate($freeText, new FreeTextShouldBeValid());
//    }
//
//    public function it_should_be_valid_when_free_text_length_is_under_limit(ExecutionContext $context): void
//    {
//        $freeText = [
//            'type' => 'free_text',
//            'string' => 'Lorem ipsum dolor sit amet',
//        ];
//
//        $command = new CreateGeneratorCommand(
//            'generatorCode',
//            [],
//            ['type' => 'free_text', 'string' => 'abcdef'],
//            ['fr' => 'Générateur'],
//            'sku',
//            '-'
//        );
//        $context->getRoot()
//            ->shouldBeCalled()
//            ->willReturn($command);
//
//        $context->buildViolation(
//            'validation.create.free_text_size_limit_reached',
//            ['{{limit}}' => 100]
//        )->shouldNotBeCalled();
//
//        $this->validate($freeText, new FreeTextShouldBeValid());
//    }
}
