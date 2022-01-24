<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class MeasurementContext implements Context
{
    public function __construct(
        private ValidatorInterface $validator,
        private CreateMeasurementFamilyHandler $createMeasurementFamilyHandler
    ) {
    }

    /**
     * @Given the :code measurement family with the :units units
     */
    public function theMeasurementFamily(string $code, string $units): void
    {
        $unitCodes = \explode(',', $units);
        $createCommand = new CreateMeasurementFamilyCommand();
        $createCommand->code = $code;
        $createCommand->labels = [];
        $createCommand->standardUnitCode = $unitCodes[0];
        $createCommand->units = \array_map(
            static fn (string $unitCode): array => [
                'code' => $unitCode,
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => $unitCode,
            ],
            $unitCodes
        );

        $violations = $this->validator->validate($createCommand);
        Assert::count($violations, 0, (string) $violations);
        $this->createMeasurementFamilyHandler->handle($createCommand);
    }
}
