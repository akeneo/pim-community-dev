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
 * Defines a thumbnail operation on an image. Width and/or height must be provided.
 */
class ThumbnailOperation implements Operation
{
    use OperationExtraParameterTrait;

    private const OPERATION_NAME = 'thumbnail';

    private ?int $width = null;

    private ?int $height = null;

    public function __construct(?int $width, ?int $height)
    {
        if (null === $width && null === $height) {
            throw new \InvalidArgumentException(
                "No parameter is provided for 'thumbnail' operation. At least one of parameter 'width' and 'height' must be defined."
            );
        }

        foreach (['width' => $width, 'height' => $height] as $parameterName => $value) {
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
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        Assert::nullOrInteger($parameters['width'] ?? null, 'Parameter "width" must be an integer.');
        Assert::nullOrInteger($parameters['height'] ?? null, 'Parameter "height" must be an integer.');

        self::assertNoExtraParameters($parameters, ['width', 'height']);

        return new self($parameters['width'] ?? null, $parameters['height'] ?? null);
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => array_filter([
                'width' => $this->width,
                'height' => $this->height,
            ]),
        ];
    }
}
