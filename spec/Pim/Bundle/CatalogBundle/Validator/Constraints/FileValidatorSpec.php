<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

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
        FileInterface $file
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $file->getExtension()->willReturn('jpg');
        $file->getSize()->willReturn(100);

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }

    function it_validates_size(
        $context,
        File $constraint,
        FileInterface $file
    ) {
        $constraint->maxSize = '1M';

        $file->getExtension()->willReturn('jpg');
        $file->getSize()->willReturn(500);

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }

    function it_does_not_validate_extensions(
        $context,
        File $constraint,
        FileInterface $file
    ) {
        $constraint->allowedExtensions = ['pdf', 'docx'];
        $file->getExtension()->willReturn('jpg');
        $file->getSize()->willReturn(100);

        $context
            ->addViolation(
                $constraint->extensionsMessage,
                ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
            )
            ->shouldBeCalled();

        $this->validate($file, $constraint);
    }

    function it_does_not_validate_size(
        $context,
        File $constraint,
        FileInterface $file
    ) {
        $constraint->maxSize = '1M';
        $file->getExtension()->willReturn('jpg');
        $file->getSize()->willReturn(1075200);
        $file->getOriginalFilename()->willReturn('my file.jpg');

        $context
            ->addViolation(
                $constraint->maxSizeMessage,
                Argument::any()
            )
            ->shouldBeCalled();

        $this->validate($file, $constraint);
    }

    function it_validates_nullable_value(
        $context,
        File $constraint
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $constraint->maxSize = '2M';

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_validates_empty_extension_and_size(
        $context,
        File $constraint,
        FileInterface $file
    ) {
        $constraint->allowedExtensions = [];
        $constraint->maxSize = null;

        $file->getExtension()->willReturn('jpg');
        $file->getSize()->willReturn(100);

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($file, $constraint);
    }
}
