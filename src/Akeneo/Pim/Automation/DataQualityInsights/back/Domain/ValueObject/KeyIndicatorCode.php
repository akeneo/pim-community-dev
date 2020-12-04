<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class KeyIndicatorCode
{
    private string $code;

    public function __construct(string $code)
    {
        if ('' === $code) {
            throw new \InvalidArgumentException('A key indicator code cannot be empty');
        }

        $this->code = $code;
    }

    public function __toString()
    {
        return $this->code;
    }
}
