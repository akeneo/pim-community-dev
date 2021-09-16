<?php

declare(strict_types=1);

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
 * Represents a certificate (or a private key) in the configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Certificate
{
    public const EXPIRATION_WARNING_IN_DAYS = 30;

    /** @var string */
    private $certificate;

    public function __construct(string $certificate)
    {
        $this->certificate = $certificate;
    }

    public function __toString(): string
    {
        return $this->certificate;
    }
}
