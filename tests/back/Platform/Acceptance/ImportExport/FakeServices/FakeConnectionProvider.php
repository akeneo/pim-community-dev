<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport\FakeServices;

use League\Flysystem\PhpseclibV2\ConnectionProvider;
use phpseclib\Net\SFTP;

final class FakeConnectionProvider implements ConnectionProvider
{
    public function __construct(
        private bool $shouldThrow
    ) {
    }

    public function provideConnection(): SFTP
    {
        if ($this->shouldThrow) {
            throw new \Exception("connection failed");
        }

        return new SFTP(
            "127.0.0.1"
        );
    }
}