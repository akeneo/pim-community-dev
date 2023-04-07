<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use PhpSpec\ObjectBehavior;

class PropertyProcessApplierSpec extends ObjectBehavior
{
    private static string $TARGET = 'sku';
    private static string $PREFIX = 'AKN-';
    private static string $SIMPLE_SELECT_ATTRIBUTE_CODE = 'size';

    public function let(
        FamilyNomenclatureRepository $familyNomenclatureRepository,
        SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
    ) {
        $this->beConstructedWith($familyNomenclatureRepository, $simpleSelectNomenclatureRepository);
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
        SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository
    ): void {
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
}
