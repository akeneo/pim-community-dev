<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindRecordsUsedAsProductVariantAxisInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\RecordShouldNotBeUsedAsProductVariantAxis;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\RecordShouldNotBeUsedAsProductVariantAxisValidator;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RecordShouldNotBeUsedAsProductVariantAxisValidatorSpec extends ObjectBehavior
{
    public function let(
        FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($findRecordsUsedAsProductVariantAxis);
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordShouldNotBeUsedAsProductVariantAxisValidator::class);
    }

    public function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_throws_an_exception_when_provided_with_an_invalid_constraint(
        DeleteRecordCommand $command
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during(
            'validate',
            [
                $command,
                new IsString(),
            ]
        );
    }

    public function it_throws_an_exception_when_provided_with_an_invalid_value()
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during(
            'validate',
            [
                'a_value',
                new RecordShouldNotBeUsedAsProductVariantAxis(),
            ]
        );
    }

    public function it_creates_a_violation_error_if_the_record_is_used_as_product_variant_axis(
        FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis,
        RecordShouldNotBeUsedAsProductVariantAxis $constraint,
        DeleteRecordCommand $command,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $command->referenceEntityIdentifier = 'color';
        $command->recordCode = 'blue';

        $findRecordsUsedAsProductVariantAxis
            ->areUsed(['blue'], 'color')
            ->shouldBeCalled()
            ->willReturn(true);

        $context
            ->buildViolation(RecordShouldNotBeUsedAsProductVariantAxis::ERROR_MESSAGE)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->addViolation()
            ->shouldBeCalled();

        $this->validate($command, $constraint);
    }

    public function it_does_nothing_if_the_record_is_not_used_as_product_variant_axis(
        FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis,
        RecordShouldNotBeUsedAsProductVariantAxis $constraint,
        DeleteRecordCommand $command,
        ExecutionContextInterface $context
    ) {
        $command->referenceEntityIdentifier = 'color';
        $command->recordCode = 'blue';

        $findRecordsUsedAsProductVariantAxis
            ->areUsed(['blue'], 'color')
            ->shouldBeCalled()
            ->willReturn(false);

        $context
            ->buildViolation()
            ->shouldNotBeCalled();

        $this->validate($command, $constraint);
    }
}
