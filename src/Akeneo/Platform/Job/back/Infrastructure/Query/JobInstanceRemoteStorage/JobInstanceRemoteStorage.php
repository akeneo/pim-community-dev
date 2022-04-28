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

namespace Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage;

class JobInstanceRemoteStorage
{
    public const PASSWORD_LOGIN_TYPE = 'password';
    public const PRIVATE_KEY_LOGIN_TYPE = 'private_key';

    private function __construct(
        private string $jobInstanceCode,
        private string $host,
        private int $port,
        private string $root,
        private string $username,
        private array $login,
    ) {
    }

    public static function create(array $jobInstanceRemoteStorage): self {
        return new self(
            $jobInstanceRemoteStorage['job_instance_code'],
            $jobInstanceRemoteStorage['host'],
            is_int($jobInstanceRemoteStorage['port']) ? $jobInstanceRemoteStorage['port'] : intval($jobInstanceRemoteStorage['port']),
            $jobInstanceRemoteStorage['root'],
            $jobInstanceRemoteStorage['username'],
            is_array($jobInstanceRemoteStorage['login']) ? $jobInstanceRemoteStorage['login'] : json_decode($jobInstanceRemoteStorage['login'], true),
        );
    }

    public function normalize(): array {
        return [
            'job_instance_code' => $this->jobInstanceCode,
            'host' => $this->host,
            'port' => $this->port,
            'root' => $this->root,
            'username' => $this->username,
            'login' => $this->login,
        ];
    }
}
