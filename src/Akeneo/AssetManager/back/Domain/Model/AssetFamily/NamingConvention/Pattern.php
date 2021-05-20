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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * Value object representing a regular expression pattern
 */
class Pattern
{
    private string $pattern;

    private function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public static function create(string $pattern): self
    {
        Assert::integer(@preg_match($pattern, ''), sprintf('The regular expression "%s" is malformed.', $pattern));

        return new self($pattern);
    }

    public function normalize(): string
    {
        return $this->pattern;
    }
}
