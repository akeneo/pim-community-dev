<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class OptimizeJpegOperation implements Operation
{
    use OperationExtraParameterTrait;

    private const OPERATION_NAME = 'optimize_jpeg';

    private int $quality;

    private function __construct(int $quality)
    {
        Assert::greaterThanEq($quality, 1, "Parameter 'quality' must be between 1 and 100.");
        Assert::lessThanEq($quality, 100, "Parameter 'quality' must be between 1 and 100.");

        $this->quality = $quality;
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        Assert::keyExists($parameters, 'quality', "The parameter 'quality' is required for the optimize jpeg operation.");
        Assert::integer($parameters['quality'], "Parameter 'quality' must be an integer.");

        self::assertNoExtraParameters($parameters, ['quality']);

        return new self($parameters['quality']);
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => ['quality' => $this->quality],
        ];
    }
}
