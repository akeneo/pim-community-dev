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

namespace Akeneo\Platform\Job\Infrastructure\Query;

class JobInstanceServerCredentials
{
    private function __construct(
        private string $jobInstanceCode,
        private string $host,
        private string $user,
        private string $password,
        private ?int $port,
        private bool $isSecure,
        private ?string $workingDirectory,
    ) {
    }

    public static function create(array $jobInstanceServerCredentials): self {
        return new self(
            $jobInstanceServerCredentials['job_instance_code'],
            $jobInstanceServerCredentials['host'],
            $jobInstanceServerCredentials['user'],
            $jobInstanceServerCredentials['password'],
            $jobInstanceServerCredentials['port'] ? (int)$jobInstanceServerCredentials['port'] : null,
            (bool)$jobInstanceServerCredentials['is_secure'],
            $jobInstanceServerCredentials['working_directory']
        );
    }

    public function normalize(): array {
        return [
            'job_instance_code' => $this->jobInstanceCode,
            'host' => $this->host,
            'user' => $this->user,
            'password' => $this->password,
            'port' => $this->port,
            'is_secure' => $this->isSecure,
            'working_directory' => $this->workingDirectory,
        ];
    }
}
