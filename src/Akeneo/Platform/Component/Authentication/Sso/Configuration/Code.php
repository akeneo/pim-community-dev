<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Simple code identifying the root of the configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Code
{
    /** @var string */
    private $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
