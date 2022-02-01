<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\RecordsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\RecordsShouldExistValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RecordsShouldExistValidatorSpec extends ObjectBehavior
{
    const COLUMNID_RECORDBRAND = 'brand_d49d3c48-46e6-4744-8196-56e08563fd46';

    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        GetExistingRecordCodes $getExistingRecordCodes,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($tableConfigurationRepository, $getExistingRecordCodes);
        $this->initialize($context);

        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                ReferenceEntityColumn::fromNormalized([
                    'id' => ColumnIdGenerator::record(),
                    'code' => 'origin',
                    'reference_entity_identifier' => 'city',
                    'is_required_for_completeness' => true,
                ]),
                TextColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                ReferenceEntityColumn::fromNormalized([
                    'id' => self::COLUMNID_RECORDBRAND,
                    'code' => 'company',
                    'reference_entity_identifier' => 'brand',
                ]),
            ])
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(RecordsShouldExistValidator::class);
    }

    function it_throws_an_exception_when_provided_with_the_wrong_constraint(ValueInterface $value)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [$value, new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_a_table_value(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new RecordsShouldExist());
    }

    function it_does_nothing_when_value_is_a_table_value_but_has_no_record_values(
        ExecutionContext $context,
        GetExistingRecordCodes $getExistingRecordCodes
    )
    {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [
                ColumnIdGenerator::record() => null,
                ColumnIdGenerator::ingredient() => 'sugar',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => null,
            ],
            [
                ColumnIdGenerator::record() => null,
                ColumnIdGenerator::ingredient() => 'vanilla',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => null,
            ],
        ]));
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, new RecordsShouldExist());
    }

    function it_does_not_build_a_violation_when_all_records_in_table_attribute_exists(
        ExecutionContext $context,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [
                ColumnIdGenerator::record() => 'Dublin',
                ColumnIdGenerator::ingredient() => 'sugar',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => 'Guiness',
            ],
            [
                ColumnIdGenerator::record() => 'Paris',
                ColumnIdGenerator::ingredient() => 'vanilla',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => 'Ladurée',
            ],
        ]));

        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [
                'city' => [
                    '0-origin' => 'Dublin',
                    '1-origin' => 'Paris',
                ],
                'brand'  => [
                    '0-company' => 'Guiness',
                    '1-company' => 'Ladurée',
                ]
            ]
        )->willReturn(
            [
                'city' => [
                    'Dublin',
                    'Paris',
                ],
                'brand' => [
                    'Guiness',
                    'Ladurée',
                ]
            ]
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, new RecordsShouldExist());
    }

    function it_builds_a_violation_when_a_record_does_not_exist(
        ExecutionContext $context,
        GetExistingRecordCodes $getExistingRecordCodes,
        ConstraintViolationBuilderInterface $violationBuilder
    )
    {
        $constraint = new RecordsShouldExist();
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [
                ColumnIdGenerator::record() => 'London',
                ColumnIdGenerator::ingredient() => 'sugar',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => 'Guiness',
            ],
            [
                ColumnIdGenerator::record() => 'Paris',
                ColumnIdGenerator::ingredient() => 'butter',
                ColumnIdGenerator::quantity() => 10,
                self::COLUMNID_RECORDBRAND => 'LU',
            ],
        ]));

        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [
                'city' => [
                    '0-origin' => 'London',
                    '1-origin' => 'Paris',
                ],
                'brand' => [
                    '0-company' => 'Guiness',
                    '1-company' => 'LU',
                ]
            ]
        )->willReturn(
            [
                'city' => [
                    'Paris',
                ],
                'brand' => [
                    'Guiness',
                ]
            ]
        );

        $context->buildViolation(
            $constraint->message,
            [
                '{{ recordCode }}' => 'London',
                '{{ referenceEntityIdentifier }}' => 'city',
            ]
        )->shouldBeCalled()->willreturn($violationBuilder);
        $violationBuilder->atPath('[0].origin')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation(
            $constraint->message,
            [
                '{{ recordCode }}' => 'LU',
                '{{ referenceEntityIdentifier }}' => 'brand',
            ]
        )->shouldBeCalled()->willreturn($violationBuilder);
        $violationBuilder->atPath('[1].company')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($tableValue, new RecordsShouldExist());
    }
}
