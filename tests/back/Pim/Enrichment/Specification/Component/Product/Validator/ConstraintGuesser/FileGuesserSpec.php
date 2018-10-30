<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\FileGuesser;

class FileGuesserSpec extends ObjectBehavior
{
    function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    public function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        foreach ($this->dataProviderForSupportedAttributes() as $attributeTypeTest) {
            $attributeType = $attributeTypeTest[0];
            $expectedResult = $attributeTypeTest[1];
            $attribute->getType()
                ->willReturn($attributeType);
            $this->supportAttribute($attribute)
                ->shouldReturn($expectedResult);
        }
    }

    function it_guesses_file_with_maxSize_integer(AttributeInterface $attribute)
    {
        $attribute->getMaxFileSize()
            ->willReturn(15)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(null)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);

        $constraint->binaryFormat->shouldBe(false);
        $constraint->maxSize
            ->shouldBe(15000000);
        $constraint->allowedExtensions
            ->shouldBe([]);
    }

    function it_guesses_file_with_maxSize_float(AttributeInterface $attribute)
    {
        $maxSize = 5.5;

        $attribute->getMaxFileSize()
            ->willReturn($maxSize)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(null)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);

        $constraint->maxSize
            ->shouldBe((int) ($maxSize * FileGuesser::KILOBYTE_MULTIPLIER * 1000));
        $constraint->allowedExtensions
            ->shouldBe([]);
    }

    function it_guesses_file_with_maxSize_numeric_string(AttributeInterface $attribute)
    {
        $maxSize = '15';

        $attribute->getMaxFileSize()
            ->willReturn($maxSize)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(null)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);

        $constraint->maxSize
            ->shouldBe(15000000);

        $maxSize = '15.5';

        $attribute->getMaxFileSize()
            ->willReturn($maxSize)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);

        $constraint->maxSize
            ->shouldBe(intval($maxSize * FileGuesser::KILOBYTE_MULTIPLIER * 1000));
    }

    function it_guesses_file_with_allowed_extensions(AttributeInterface $attribute)
    {
        $allowedExtensions = ['jpg', 'png'];

        $attribute->getMaxFileSize()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn($allowedExtensions)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);
        $constraint->maxSize
            ->shouldBe(null);
        $constraint->allowedExtensions
            ->shouldBe($allowedExtensions);
    }

    function it_guesses_file_with_multiple_options(AttributeInterface $attribute)
    {
        $attribute->getMaxFileSize()
            ->willReturn(15)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(['jpg', 'png'])
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldHaveCount(1);

        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(File::class);

        $constraint->maxSize
            ->shouldBe(15000000);

        $constraint->allowedExtensions
            ->shouldBe(['jpg', 'png']);
    }

    function it_does_not_guess_file_with_empty_allowed_extensions(AttributeInterface $attribute)
    {
        $attribute->getMaxFileSize()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn([])
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_file_without_options(AttributeInterface $attribute)
    {
        $attribute->getMaxFileSize()
            ->willReturn(null)
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(null)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
        $attribute->getMaxFileSize()
            ->willReturn('not_a_numeric_value')
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    function it_does_not_guess_file_with_invalid_maxsize(AttributeInterface $attribute)
    {
        $attribute->getMaxFileSize()
            ->willReturn('not_a_numeric_value')
            ->shouldBeCalled();
        $attribute->getAllowedExtensions()
            ->willReturn(null)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);

        $attribute->getMaxFileSize()
            ->willReturn(0)
            ->shouldBeCalled();
        $constraints = $this->guessConstraints($attribute);

        $constraints->shouldReturn([]);
    }

    private function dataProviderForSupportedAttributes()
    {
        return [
            ['pim_catalog_file', true],
            ['pim_catalog_image', true],
            ['pim_catalog_text', false],
        ];
    }
}
