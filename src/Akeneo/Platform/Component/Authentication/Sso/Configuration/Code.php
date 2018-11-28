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

    private function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }
}
