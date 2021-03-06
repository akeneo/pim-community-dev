<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileValidator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FileValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $extensionsToMimeTypeMapping = [
            'jfif' => ['image/jpeg'],
            'csv' => ['text/plain', 'text/csv']
        ];

        $this->beConstructedWith($extensionsToMimeTypeMapping);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->beAnInstanceOf(ConstraintValidator::class);
    }

    function it_validates_extensions_and_mimetype(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(100);
        $fileInfo->getMimeType()->willReturn('image/jpeg');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($fileInfo, $constraint);
    }

    function it_validates_size(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo
    ) {
        $constraint->maxSize = '1M';

        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(500);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($fileInfo, $constraint);
    }

    function it_does_not_validate_extensions(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $extension = 'jpg';
        $constraint->allowedExtensions = ['pdf', 'docx'];
        $constraint->attributeCode = 'a_code';
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn($extension);
        $fileInfo->getSize()->willReturn(100);
        $fileInfo->getMimeType()->willReturn('image/jpeg');

        $context
            ->buildViolation(
                $constraint->extensionsMessage,
                [
                '%extensions%' => implode(', ', $constraint->allowedExtensions),
                    '%type%' => $extension,
                    '%attribute%' => $constraint->attributeCode,
                ]
            )
            ->shouldBeCalledTimes(1)
            ->willReturn($violationBuilder);
        $violationBuilder->setCode(File::EXTENSION_NOT_ALLOWED_ERROR)
            ->shouldBeCalledTimes(1)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(1)->willReturn($violationBuilder);

        $this->validate($fileInfo, $constraint);
    }

    function it_does_not_validate_size(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo,
        ConstraintViolationBuilderInterface $violation,
        AbstractValue $abstractValue
    ) {
        $constraint->maxSize = '1M';
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(1075200);
        $fileInfo->getOriginalFilename()->willReturn('my file.jpg');
        $abstractValue->getAttributeCode()->willReturn('a_attribute_code');

        $context->getObject()->willReturn($abstractValue);

        $context
            ->buildViolation($constraint->maxSizeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->setParameter('%file_name%', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%file_size%', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%max_file_size%', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%suffix%', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%attribute%', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($fileInfo, $constraint);
    }

    function it_validates_new_instance_of_files(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $constraint->maxSize = '2M';

        $fileInfo->getId()->willReturn(null);
        $fileInfo->getUploadedFile()->willReturn(null);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_validates_nullable_value($context, File $constraint)
    {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $constraint->maxSize = '2M';

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_validates_empty_extension_and_size($context, File $constraint, FileInfoInterface $fileInfo)
    {
        $constraint->allowedExtensions = [];

        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(100);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($fileInfo, $constraint);
    }

    function it_does_not_validate_extensions_if_mimetype_is_not_coherent(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->allowedExtensions = ['jfif', 'docx'];
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jfif');
        $fileInfo->getSize()->willReturn(100);
        $fileInfo->getMimeType()->willReturn('application/octet-stream');

        $context
            ->buildViolation(
                $constraint->mimeTypeMessage,
                [
                    '%extension%' => 'jfif',
                    '%types%'     => 'image/jpeg',
                    '%type%'      => 'application/octet-stream',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($fileInfo, $constraint);
    }
}
