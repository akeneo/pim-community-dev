<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MeasurementFamilyCode
{
    private string $code;

    private function __construct(string $code)
    {
        Assert::stringNotEmpty($code);
        Assert::maxLength(
            $code,
            255,
            sprintf('Measurement family code cannot be longer than 255 characters, %d string long given', strlen($code))
        );
        Assert::regex(
            $code,
            '/^[a-zA-Z0-9_]+$/',
            sprintf('Measurement family code may contain only letters, numbers and underscores. "%s" given', $code)
        );

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function normalize(): string
    {
        return $this->code;
    }
}
