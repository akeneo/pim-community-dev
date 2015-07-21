<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
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

    function it_validates_extensions($context, File $constraint)
    {
        $constraint->allowedExtensions = ['gif', 'jpg'];

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
            $constraint
        );
    }

    function it_does_not_validate_extensions(
        $context,
        File $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->allowedExtensions = ['pdf', 'docx'];

        $context
            ->buildViolation(
                $constraint->extensionsMessage,
                ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
            $constraint
        );
    }

    function it_validates_extensions_with_product_media(
        $context,
        File $constraint,
        ProductMediaInterface $productMedia
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];
        $productMedia->getFile()->willReturn(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg')
        );

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productMedia, $constraint);
    }

    function it_validates_nullable_value($context, File $constraint)
    {
        $constraint->allowedExtensions = ['gif', 'jpg'];

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_validates_empty_extensions($context, File $constraint)
    {
        $constraint->allowedExtensions = [];

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/caterpillar_variant_import.zip'),
            $constraint
        );
    }
}
