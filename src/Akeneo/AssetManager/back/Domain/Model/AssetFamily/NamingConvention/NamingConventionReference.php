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

class NamingConventionReference
{
    /** @var ?NamingConvention */
    private $namingConvention;

    private function __construct(?NamingConvention $namingConvention)
    {
        $this->namingConvention = $namingConvention;
    }

    public static function createFromNormalized(?string $normalizedNamingConvention): NamingConventionReference
    {
        if (null === $normalizedNamingConvention) {
            return self::noNamingConvention();
        }

        return new self(NamingConvention::createFromNormalized($normalizedNamingConvention));
    }

    public static function noNamingConvention(): NamingConventionReference
    {
        return new self(null);
    }

    public function normalize(): ?array
    {
        if (null === $this->namingConvention) {
            return null;
        }

        return $this->namingConvention->normalize();
    }
}
