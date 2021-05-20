<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Webmozart\Assert\Assert;

/**
 * Define the resolution change of an image.
 */
class ResolutionOperation implements Operation
{
    use OperationExtraParameterTrait;

    private const OPERATION_NAME = 'resolution';
    private const RESOLUTION_UNIT_CHOICES = ['ppc', 'ppi'];

    private int $resolutionX;

    private int $resolutionY;

    private string $resolutionUnit;

    private function __construct(int $resolutionX, int $resolutionY, string $resolutionUnit)
    {
        Assert::oneOf($resolutionUnit, self::RESOLUTION_UNIT_CHOICES, sprintf(
            "Parameter 'resolution-unit' must be one of this values: '%s'. '%s' given.",
            implode(', ', self::RESOLUTION_UNIT_CHOICES),
            $resolutionUnit
        ));

        foreach (['resolution-x' => $resolutionX, 'resolution-y' => $resolutionY] as $parameterName => $resolution) {
            if ($resolution <= 0) {
                throw new \InvalidArgumentException(sprintf(
                    "Parameter '%s' must be an integer greater than 0. '%d' given.",
                    $parameterName,
                    $resolution
                ));
            }
        }

        $this->resolutionX = $resolutionX;
        $this->resolutionY = $resolutionY;
        $this->resolutionUnit = $resolutionUnit;
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        $missingParameterMessage = "The parameters 'resolution-x', 'resolution-y' and 'resolution-unit' are required for the resolution operation.";
        Assert::keyExists($parameters, 'resolution-x', $missingParameterMessage);
        Assert::keyExists($parameters, 'resolution-y', $missingParameterMessage);
        Assert::keyExists($parameters, 'resolution-unit', $missingParameterMessage);
        Assert::string($parameters['resolution-unit'], "Parameter 'resolution-unit' must be a string.");
        Assert::integer($parameters['resolution-x'], "Parameter 'resolution-x' must be an integer.");
        Assert::integer($parameters['resolution-y'], "Parameter 'resolution-y' must be an integer.");

        self::assertNoExtraParameters($parameters, ['resolution-x', 'resolution-y', 'resolution-unit']);

        return new self($parameters['resolution-x'] ?? null, $parameters['resolution-y'] ?? null, $parameters['resolution-unit']);
    }

    public function getResolutionX(): int
    {
        return $this->resolutionX;
    }

    public function getResolutionY(): int
    {
        return $this->resolutionY;
    }

    public function getResolutionUnit(): string
    {
        return $this->resolutionUnit;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => [
                'resolution-x' => $this->resolutionX,
                'resolution-y' => $this->resolutionY,
                'resolution-unit' => $this->resolutionUnit,
            ],
        ];
    }
}
