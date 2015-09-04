<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FileValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\FileValidator');
    }

    function it_validates_extensions(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(100);

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
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->allowedExtensions = ['pdf', 'docx'];
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(100);

        $context
            ->buildViolation(
                $constraint->extensionsMessage,
                ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($fileInfo, $constraint);
    }

    function it_does_not_validate_size(
        $context,
        File $constraint,
        FileInfoInterface $fileInfo,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->maxSize = '1M';
        $fileInfo->getId()->willReturn(12);
        $fileInfo->getUploadedFile()->willReturn(null);
        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getSize()->willReturn(1075200);
        $fileInfo->getOriginalFilename()->willReturn('my file.jpg');

        $context
            ->buildViolation($constraint->maxSizeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->setParameter('{{ file }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ size }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ suffix }}', Argument::any())->shouldBeCalled()->willReturn($violation);
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
}
