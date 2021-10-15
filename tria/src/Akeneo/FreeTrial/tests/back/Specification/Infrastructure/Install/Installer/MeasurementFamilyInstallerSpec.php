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

namespace Specification\Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MeasurementFamilyInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixtureReader,
        ValidatorInterface $validator,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler,
        CreateMeasurementFamilyHandler $createMeasurementFamilyHandler,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $this->beConstructedWith($fixtureReader, $validator, $saveMeasurementFamilyHandler, $createMeasurementFamilyHandler, $measurementFamilyRepository);
    }

    public function it_successfully_installs_measurement_families(
        $fixtureReader,
        $validator,
        $saveMeasurementFamilyHandler,
        $createMeasurementFamilyHandler,
        $measurementFamilyRepository,
        MeasurementFamily $measurementToUpdate
    ) {
        $measurementToCreateData = [
            'code' => 'power_consumption',
            'standard_unit_code' => 'kw_h',
            'units' => [
                'kw_h' => [
                    'code' => 'kw_h',
                    'labels' => ['en_US' => 'Kilowatts-hour'],
                    'symbol' => 'kWh',
                    'convert_from_standard' => [
                        'operator' => 'mul',
                        'value' => '1',
                    ],
                ],
            ],
        ];

        $measurementToUpdateData = [
            'code' => 'angle',
            'standard_unit_code' => 'RADIAN',
            'units' => [
                'RADIAN' => [
                    'code' => 'RADIAN',
                    'labels' => ['en_US' => 'Radian'],
                    'symbol' => 'rad',
                    'convert_from_standard' => [
                        'operator' => 'mul',
                        'value' => '1',
                    ],
                ],
            ],
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$measurementToCreateData, $measurementToUpdateData]));

        $measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString('power_consumption'))
            ->willThrow(new MeasurementFamilyNotFoundException());

        $measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString('angle'))
            ->willReturn($measurementToUpdate);

        $measurementToUpdate->normalizeWithIndexedUnits()->willReturn([
            'code' => 'angle',
            'standard_unit_code' => 'RADIAN',
            'labels' => ['en_US' => 'Radian'],
            'units' => [],
        ]);

        $validator->validate(Argument::any())->willReturn(new ConstraintViolationList([]));

        $createMeasurementFamilyHandler->handle(Argument::that(function ($createMeasurementCommand) {
            return $createMeasurementCommand instanceof CreateMeasurementFamilyCommand
                && $createMeasurementCommand->code === 'power_consumption';
        }))->shouldBeCalled();

        $saveMeasurementFamilyHandler->handle(Argument::that(function ($saveMeasurementCommand) {
            return $saveMeasurementCommand instanceof SaveMeasurementFamilyCommand
                && $saveMeasurementCommand->code === 'angle';
        }))->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_measurement_family_to_create_is_invalid(
        $fixtureReader,
        $validator,
        $createMeasurementFamilyHandler,
        $measurementFamilyRepository,
        ConstraintViolationInterface $violation
    ) {
        $measurementToCreateData = [
            'code' => 'invalid',
            'standard_unit_code' => 'foo',
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$measurementToCreateData]));

        $measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString('invalid'))
            ->willThrow(new MeasurementFamilyNotFoundException());

        $validator->validate(Argument::any())->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $createMeasurementFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\Exception::class)->during('install');
    }

    public function it_throws_an_exception_if_a_measurement_family_to_update_is_invalid(
        $fixtureReader,
        $validator,
        $saveMeasurementFamilyHandler,
        $measurementFamilyRepository,
        MeasurementFamily $measurementToUpdate,
        ConstraintViolationInterface $violation
    ) {
        $measurementToUpdateData = [
            'code' => 'invalid',
            'standard_unit_code' => 'foo',
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$measurementToUpdateData]));

        $measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString('invalid'))
            ->willReturn($measurementToUpdate);

        $measurementToUpdate->normalizeWithIndexedUnits()->willReturn([
            'code' => 'angle',
            'standard_unit_code' => 'RADIAN',
            'labels' => [],
        ]);

        $validator->validate(Argument::any())->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $saveMeasurementFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\Exception::class)->during('install');
    }
}
