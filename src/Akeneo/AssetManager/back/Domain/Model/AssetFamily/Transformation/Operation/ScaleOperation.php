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
 * Define a scale operation on an image.
 * 3 parameters can be provided: width, height and ratioPercent. At least 1 is mandatory.
 * The proportion is always kept. That's mean if width and height are provided, it takes the maximum value
 * in order to keep the proportion.
 */
class ScaleOperation implements Operation
{
    use OperationExtraParameterTrait;

    private const OPERATION_NAME = 'scale';

    private ?int $width = null;

    private ?int $height = null;

    private ?int $ratioPercent = null;

    private function __construct(?int $width, ?int $height, ?int $ratioPercent)
    {
        if (null === $width && null === $height && null === $ratioPercent) {
            throw new \InvalidArgumentException(
                "No parameter is provided for 'scale' operation. At least one of parameter 'width', 'height' and 'ratio' must be defined."
            );
        }

        foreach (['width' => $width, 'height' => $height, 'ratio' => $ratioPercent] as $parameterName => $value) {
            if (null === $value) {
                continue;
            }

            if ($value <= 0) {
                throw new \InvalidArgumentException(sprintf(
                    'Parameter "%s" must be greater than 0, "%d" given.',
                    $parameterName,
                    $value
                ));
            }
        }

        $this->width = $width;
        $this->height = $height;
        $this->ratioPercent = $ratioPercent;
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        foreach (['width', 'height', 'ratio'] as $parameterName) {
            if (!array_key_exists($parameterName, $parameters)) {
                continue;
            }

            Assert::nullOrInteger($parameters[$parameterName], sprintf(
                'Parameter "%s" must be an integer.',
                $parameterName
            ));
        }

        self::assertNoExtraParameters($parameters, ['width', 'height', 'ratio']);

        return new self($parameters['width'] ?? null, $parameters['height'] ?? null, $parameters['ratio'] ?? null);
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getRatioPercent(): ?int
    {
        return $this->ratioPercent;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => array_filter([
                'width' => $this->width,
                'height' => $this->height,
                'ratio' => $this->ratioPercent,
            ]),
        ];
    }
}
