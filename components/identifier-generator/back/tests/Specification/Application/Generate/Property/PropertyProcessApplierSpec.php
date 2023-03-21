<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureRepository;
use PhpSpec\ObjectBehavior;

class PropertyProcessApplierSpec extends ObjectBehavior
{
    private static $TARGET = 'sku';
    private static $PREFIX = 'AKN-';
    private static $NOMENCLATURE_PROPERTY_CODE = 'family';

    public function let(
        NomenclatureRepository $nomenclatureRepository
    ) {
        $this->beConstructedWith($nomenclatureRepository);
    }

    public function it_should_return_code_without_truncate(): void
    {
        $this->apply(
            Process::fromNormalized([
                'type' => 'no',
            ]),
            self::$NOMENCLATURE_PROPERTY_CODE,
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
            self::$NOMENCLATURE_PROPERTY_CODE,
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
            self::$NOMENCLATURE_PROPERTY_CODE,
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
                self::$NOMENCLATURE_PROPERTY_CODE,
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
            self::$NOMENCLATURE_PROPERTY_CODE,
            'fam',
            self::$TARGET,
            self::$PREFIX
        )->shouldReturn('fam');
    }

    public function it_should_throw_an_error_if_nomenclature_doesnt_exist(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(UndefinedNomenclatureException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$NOMENCLATURE_PROPERTY_CODE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_doesnt_have_value_and_no_flag_generate_if_empty(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(UndefinedNomenclatureException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$NOMENCLATURE_PROPERTY_CODE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_is_too_small(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('=', 3, false, ['familyCode' => 'ab']);
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->shouldThrow(UnableToTruncateException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$NOMENCLATURE_PROPERTY_CODE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_throw_an_error_if_nomenclature_is_too_long(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abcd']);
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->shouldThrow(UnableToTruncateException::class)->during(
            'apply',
            [
                Process::fromNormalized([
                    'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                ]),
                self::$NOMENCLATURE_PROPERTY_CODE,
                'familyCode',
                self::$TARGET,
                self::$PREFIX,
            ]
        );
    }

    public function it_should_return_code_with_valid_nomenclature_value(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abc']);
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            self::$NOMENCLATURE_PROPERTY_CODE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('abc');
    }

    public function it_should_return_code_with_empty_nomenclature_value_and_flag_generate_if_empty(NomenclatureRepository $nomenclatureRepository): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, true, []);
        $nomenclatureRepository
            ->get(self::$NOMENCLATURE_PROPERTY_CODE)
            ->shouldBeCalledOnce()
            ->willReturn($nomenclature);

        $this->apply(
            Process::fromNormalized([
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ]),
            self::$NOMENCLATURE_PROPERTY_CODE,
            'familyCode',
            self::$TARGET,
            self::$PREFIX,
        )->shouldReturn('fam');
    }
}
