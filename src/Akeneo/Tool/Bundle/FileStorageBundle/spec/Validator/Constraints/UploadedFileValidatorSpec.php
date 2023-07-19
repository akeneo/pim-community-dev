<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class UploadedFileValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Constraints\UploadedFileValidator::class);
    }

    public function it_validates_a_correct_file(
        File\UploadedFile $file,
        ExecutionContextInterface $context
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.PNG', 'image/png', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('PNG');
        $file->getMimeType()->willReturn('image/png');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }

    public function it_validates_a_file_with_supported_type_and_extension_even_if_they_dont_match(
        File\UploadedFile $file,
        ExecutionContextInterface $context
    ): void {
        $file->beConstructedWith([__FILE__, 'akeneo.ai', 'application/illustrator', null, true]);

        $file->guessExtension()->willReturn('pdf');
        $file->getClientOriginalExtension()->willReturn('ai');
        $file->getMimeType()->willReturn('application/pdf');
        $file->getClientMimeType()->willReturn('application/illustrator');

        $constraint = new Constraints\UploadedFile([
            'types' => ['ai' => ['application/illustrator'], 'pdf' => ['application/pdf']],
        ]);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_client_extension_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $file->beConstructedWith([__FILE__, 'akeneo.jpg', 'image/png', null, true]);

        $file->guessExtension()->willReturn('jpg');
        $file->getClientOriginalExtension()->willReturn('jpg');
        $file->getMimeType()->willReturn('image/png');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->unsupportedExtensionMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ extension }}', 'jpg')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ extensions }}', 'png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_client_mime_type_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/jpg', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/jpg');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_guessed_extension_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/png', null, true]);

        $file->guessExtension()->willReturn('jpg');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/png');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_guessed_mime_type_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/png', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }
}
