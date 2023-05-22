<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnexpectedAttributeTypeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class PropertyProcessApplierSpec extends ObjectBehavior
{
    private static string $TARGET = 'sku';
    private static string $PREFIX = 'AKN-';
    private static string $SIMPLE_SELECT_ATTRIBUTE_CODE = 'size';
    private static string $REF_ENTITY_ATTRIBUTE_CODE = 'brand';

    public function let(
        FamilyNomenclatureRepository $familyNomenclatureRepository,
        SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
        GetAttributes $getAttributes,
        ReferenceEntityNomenclatureRepository $referenceEntityNomenclatureRepository,
    ) {
        $this->beConstructedWith(
            $familyNomenclatureRepository,
            $simpleSelectNomenclatureRepository,
            $getAttributes,
            $referenceEntityNomenclatureRepository,
        );
    }

    public function it_should_return_code_without_truncate(): void
    {
        $this->apply(
            Process::fromNormalized([
                'type' => 'no',
            ]),
            FamilyProperty::TYPE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('familyCode');
    }

    public function it_should_return_code_with_truncate(): void
    {
        $this->apply(
            Process::fromNormalized([
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ]),
            FamilyProperty::TYPE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('fam');
    }

    public function it_should_return_code_with_truncate_and_smaller_code(): void
    {
        $this->apply(
            Process::fromNormalized([
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ]),
            FamilyProperty::TYPE,
            'fa',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('fa');
    }

    public function it_should_throw_an_error_if_code_is_too_small(): void
    {
        $this->shouldThrow(new UnableToTruncateException('AKN-fam', self::$TARGET, 'fam'))->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => 'truncate',
                    'operator' => Process::PROCESS_OPERATOR_EQ,
                    'value' => 4,
                ]),
                FamilyProperty::TYPE,
                'fam',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_not_throw_an_error_if_code_is_exactly_the_right_length(): void
    {
        $this->apply(
            Process::fromNormalized([
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 3,
            ]),
            FamilyProperty::TYPE,
            'fam',
            self::$TARGET,
            self::$PREFIX
        )->shouldReturn('fam');
    }

    public function it_should_throw_an_error_if_nomenclature_doesnt_exist(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(UndefinedNomenclatureException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                FamilyProperty::TYPE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_doesnt_have_value_and_no_flag_generate_if_empty(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(UndefinedNomenclatureException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                FamilyProperty::TYPE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_is_too_small(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('=', 3, false, ['familyCode' => 'ab']);
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->shouldThrow(UnableToTruncateException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                FamilyProperty::TYPE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_is_too_long(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abcd']);
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->shouldThrow(UnableToTruncateException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                FamilyProperty::TYPE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_return_code_with_valid_nomenclature_value(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abc']);
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            FamilyProperty::TYPE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('abc');
    }

    public function it_should_return_simple_select_code_with_valid_nomenclature_value(
        SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
        GetAttributes $getAttributes,
    ): void {
        $simpleSelectAttribute = new Attribute(
            self::$SIMPLE_SELECT_ATTRIBUTE_CODE,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            [],
            false,
            []
        );
        $getAttributes
            ->forCode(self::$SIMPLE_SELECT_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($simpleSelectAttribute);
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['l' => 'lar']);
        $simpleSelectNomenclatureRepository
            ->get(self::$SIMPLE_SELECT_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            self::$SIMPLE_SELECT_ATTRIBUTE_CODE,
            'l',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('lar');
    }

    public function it_should_return_code_with_empty_nomenclature_value_and_flag_generate_if_empty(FamilyNomenclatureRepository $familyNomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, true, []);
        $familyNomenclatureRepository
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            FamilyProperty::TYPE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('fam');
    }

    public function it_should_return_reference_entity_code_with_valid_nomenclature_value(
        GetAttributes $getAttributes,
        ReferenceEntityNomenclatureRepository $referenceEntityNomenclatureRepository,
    ): void {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['blue' => 'bl']);
        $refEntityAttribute = new Attribute(
            self::$REF_ENTITY_ATTRIBUTE_CODE,
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            [],
            false,
            []
        );
        $getAttributes
            ->forCode(self::$REF_ENTITY_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($refEntityAttribute);
        $referenceEntityNomenclatureRepository
            ->get(self::$REF_ENTITY_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            self::$REF_ENTITY_ATTRIBUTE_CODE,
            'blue',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('bl');
    }

    public function it_should_throw_an_error_if_property_attribute_code_does_not_exists(
        GetAttributes $getAttributes
    ): void
    {
        $getAttributes
            ->forCode(self::$REF_ENTITY_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn(null);

        $this->shouldThrow(UndefinedAttributeException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$REF_ENTITY_ATTRIBUTE_CODE,
                'value',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_property_attribute_type_is_not_expected(
        GetAttributes $getAttributes
    ): void
    {
        $unexpectedAttribute = new Attribute(
            'unexpectedAttribute',
            AttributeTypes::TEXT,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            [],
            false,
            []
        );
        $getAttributes
            ->forCode(self::$REF_ENTITY_ATTRIBUTE_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($unexpectedAttribute);

        $this->shouldThrow(UnexpectedAttributeTypeException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$REF_ENTITY_ATTRIBUTE_CODE,
                'value',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }
}
