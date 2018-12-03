<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Represents a certificate (e.g. a public or private key) in the configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Certificate
{
    /** @var string */
    private $certificate;

    private function __construct(string $certificate)
    {
        $this->certificate = $certificate;
    }

    public static function fromString(string $certificate): self
    {
        return new self($certificate);
    }

    public function toString(): string
    {
        return $this->certificate;
    }
}
