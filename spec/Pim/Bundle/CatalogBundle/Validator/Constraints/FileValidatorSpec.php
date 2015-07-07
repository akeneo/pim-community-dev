<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File;
use Pim\Bundle\CatalogBundle\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\ExecutionContextInterface;
use Prophecy\Argument;

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
        File $constraint
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
            $constraint
        );
    }

    function it_does_not_validate_extensions(
        $context,
        File $constraint
    ) {
        $constraint->allowedExtensions = ['pdf', 'docx'];

        $context
            ->addViolation(
                $constraint->extensionsMessage,
                ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
            )
            ->shouldBeCalled();

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
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productMedia, $constraint);
    }

    function it_validates_nullable_value(
        $context,
        File $constraint
    ) {
        $constraint->allowedExtensions = ['gif', 'jpg'];

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_validates_empty_extensions(
        $context,
        File $constraint
    ) {
        $constraint->allowedExtensions = [];

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(
            new \SplFileInfo(__DIR__.'/../../../../../../features/Context/fixtures/caterpillar_variant_import.zip'),
            $constraint
        );
    }
}
