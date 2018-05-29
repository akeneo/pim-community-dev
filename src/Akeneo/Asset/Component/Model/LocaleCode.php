<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Model;

/**
 * Value object that represent a locale code in this bounded context.
 */
final class LocaleCode
{
    private const DEFAULT_VALUE = 'no-locale';

    /** @var string */
    private $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = '' === $code ? self::DEFAULT_VALUE : $code;
    }

    /**
     * @return bool
     */
    public function hasValidCode(): bool
    {
        return self::DEFAULT_VALUE === $this->code;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }
}
