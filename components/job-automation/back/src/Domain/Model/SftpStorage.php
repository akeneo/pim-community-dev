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
    public const LOGIN_TYPES = [self::LOGIN_TYPE_PASSWORD, self::LOGIN_TYPE_PRIVATE_KEY];
    public const LOGIN_TYPE_PASSWORD = 'password';
    public const LOGIN_TYPE_PRIVATE_KEY = 'private_key';

    public function __construct(
        private string $host,
        private int $port,
        private string $loginType,
        private string $username,
        private ?string $password,
        private string $filePath,
        private ?string $privateKey,
        private ?string $publicKey,
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

    public function getPort(): int
    {
        return $this->port;
    }

    public function getLoginType(): string
    {
        return $this->loginType;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }
}
