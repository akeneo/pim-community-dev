<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\RecordsShouldExist;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\RecordsShouldExistValidator;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RecordsShouldExistValidatorSpec extends ObjectBehavior
{
    function let(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        AttributeInterface $designers,
        AttributeInterface $mainColor
    ) {
        $designers->getReferenceDataName()->willReturn('designers');
        $attributeRepository->findOneByIdentifier('designers')->willReturn($designers);
        $mainColor->getReferenceDataName()->willReturn('color');
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($mainColor);

        $this->beConstructedWith($findExistingRecordCodes, $attributeRepository);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordsShouldExistValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['a_value', new IsString()]);
    }

    function it_only_validates_value_collections(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        ExecutionContextInterface $context
    ) {
        $findExistingRecordCodes->find(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new RecordsShouldExist());
    }

    function it_does_not_validate_the_values_if_there_are_no_reference_enetity_values(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        ExecutionContextInterface $context
    ) {
        $findExistingRecordCodes->find(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'my_identifier'),
                ScalarValue::localizableValue('description', 'en_US', 'An awesome description'),
                DateValue::value('release_date', new \DateTime()),
            ]
        );

        $this->validate($values, new RecordsShouldExist());
    }

    function it_does_not_build_any_violation_if_every_record_exists(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        ExecutionContextInterface $context
    ) {
        $findExistingRecordCodes
            ->find(
                Argument::that(function($collaborator) {
                    return $collaborator instanceof ReferenceEntityIdentifier &&
                        'designers' === $collaborator->normalize();
                }),
                ['starck', 'dyson']
            )->shouldBeCalled()->willReturn(['starck', 'dyson']);

        $findExistingRecordCodes
            ->find(
                Argument::that(
                    function ($collaborator) {
                        return $collaborator instanceof ReferenceEntityIdentifier &&
                            'color' === $collaborator->normalize();
                    }
                ),
                ['red', 'blue']
            )->shouldBeCalled()->willReturn(['red', 'blue']);

        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'My great product'),
                    ReferenceEntityCollectionValue::localizableValue(
                        'designers',
                        [RecordCode::fromString('starck')],
                        'en_US'
                    ),
                    ReferenceEntityCollectionValue::localizableValue(
                        'designers',
                        [RecordCode::fromString('dyson'), RecordCode::fromString('starck')],
                        'fr_FR'
                    ),
                    ReferenceEntityValue::localizableValue('main_color', RecordCode::fromString('red'), 'en_US'),
                    ReferenceEntityValue::localizableValue('main_color', RecordCode::fromString('blue'), 'fr_FR'),
                ]
            ),
            new RecordsShouldExist()
        );
    }

    function it_adds_violations_if_records_do_not_exist(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationBuilderInterface $secondViolationBuilder
    ) {
        $constraint = new RecordsShouldExist();
        $values = new WriteValueCollection(
            [
                ScalarValue::value('name', 'My great product'),
                ReferenceEntityCollectionValue::localizableValue(
                    'designers',
                    [RecordCode::fromString('starck')],
                    'en_US'
                ),
                ReferenceEntityCollectionValue::localizableValue(
                    'designers',
                    [RecordCode::fromString('newson'), RecordCode::fromString('starck'), RecordCode::fromString('arad')],
                    'fr_FR'
                ),
                ReferenceEntityValue::localizableValue('main_color', RecordCode::fromString('red'), 'en_US'),
                ReferenceEntityValue::localizableValue('main_color', RecordCode::fromString('blue'), 'fr_FR'),
            ]
        );

        $findExistingRecordCodes
            ->find(
                Argument::that(
                    function ($collaborator) {
                        return $collaborator instanceof ReferenceEntityIdentifier &&
                            'designers' === $collaborator->normalize();
                    }
                ),
                ['starck', 'newson', 'arad']
            )->shouldBeCalled()->willReturn(['starck']);

        $findExistingRecordCodes
            ->find(
                Argument::that(
                    function ($collaborator) {
                        return $collaborator instanceof ReferenceEntityIdentifier &&
                            'color' === $collaborator->normalize();
                    }
                ),
                ['red', 'blue']
            )->shouldBeCalled()->willReturn(['blue']);

        $context->buildViolation(
            $constraint->messagePlural,
            [
                '%attribute_code%' => 'designers',
                '%invalid_records%' => 'newson, arad'
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[designers-<all_channels>-fr_FR]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation(
            $constraint->message,
            [
                '%attribute_code%' => 'main_color',
                '%invalid_record%' => 'red'
            ]
        )->shouldBeCalled()->willReturn($secondViolationBuilder);
        $secondViolationBuilder->atPath('[main_color-<all_channels>-en_US]')->willReturn($secondViolationBuilder);
        $secondViolationBuilder->addViolation()->shouldBecalled();

        $this->validate($values, $constraint);
    }
}
