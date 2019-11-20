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
 * Define resize image operation. All values are in pixels.
 */
class ResizeOperation implements Operation
{
    private const OPERATION_NAME = 'resize';

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    private function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        Assert::isArray($parameters);

        foreach (['width', 'height'] as $parameter) {
            Assert::keyExists($parameters, $parameter, sprintf("Key '%s' must exist in parameters.", $parameter));
            Assert::integer($parameters[$parameter], sprintf("Parameter '%s' must be an integer.", $parameter));
        }

        return new self($parameters['width'], $parameters['height']);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => ['width' => $this->width, 'height' => $this->height],
        ];
    }
}
