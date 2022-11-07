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

namespace Akeneo\Platform\JobAutomation\Domain\Model;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;

final class SftpStorage implements StorageInterface
{
    public const TYPE = 'sftp';

    public function __construct(
        private string $host,
        private int $port,
        private string $username,
        private string $password,
        private string $filePath,
        private ?string $fingerprint = null,
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }
}
