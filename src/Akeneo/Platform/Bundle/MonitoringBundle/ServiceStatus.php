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

namespace Akeneo\Platform\Bundle\MonitoringBundle;

/**
 * Value object to hold service status information
 *
 * @author Benoit Jacquemont <benoit@akeneo.com>
 */
final class ServiceStatus
{
    /** @var bool */
    private $ok;

    /** @var string */
    private $message;

    public function __construct(
        bool $ok,
        string $message
    ) {
        $this->ok = $ok;
        $this->message = $message;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
