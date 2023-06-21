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
    public function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Constraints\UploadedFileValidator::class);
    }

    public function it_validates_the_file(
        File\UploadedFile $file,
        ExecutionContextInterface $context
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/png', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/png');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_extension_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.jpg', 'image/jpg', null, true]);

        $file->guessExtension()->willReturn('jpg');
        $file->getClientOriginalExtension()->willReturn('jpg');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/jpg');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->unsupportedExtensionMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ extension }}', 'jpg')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ extensions }}', 'png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_mime_type_is_not_supported(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/jpg', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/jpg');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->unsupportedMimeTypeMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ mimeType }}', 'image/jpg')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ mimeTypes }}', 'image/png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_extension_does_not_match_file_content(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/png', null, true]);

        $file->guessExtension()->willReturn('jpg');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/png');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->invalidExtensionMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ originalExtension }}', 'png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_mime_type_does_not_match_file_content(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/png', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/png');

        $constraint = new Constraints\UploadedFile([
            'types' => ['png' => ['image/png']],
        ]);

        $context->buildViolation($constraint->invalidMimeTypeMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ originalMimeType }}', 'image/png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }

    public function it_adds_violation_if_mime_type_does_not_match_extension(
        File\UploadedFile $file,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $file->beConstructedWith([__FILE__, 'akeneo.png', 'image/jpg', null, true]);

        $file->guessExtension()->willReturn('png');
        $file->getClientOriginalExtension()->willReturn('png');
        $file->getMimeType()->willReturn('image/jpg');
        $file->getClientMimeType()->willReturn('image/jpg');

        $constraint = new Constraints\UploadedFile([
            'types' => [
                'png' => ['image/png'],
                'jpg' => ['image/jpg'],
            ],
        ]);

        $context->buildViolation($constraint->mimeTypeDoesNotMatchExtensionMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ originalMimeType }}', 'image/jpg')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ originalExtension }}', 'png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($file, $constraint);
    }
}
