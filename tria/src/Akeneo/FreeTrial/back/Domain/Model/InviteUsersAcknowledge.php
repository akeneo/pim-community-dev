<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Domain\Model;

final class InviteUsersAcknowledge
{
    private bool $success;

    private array $errors;

    public function __construct()
    {
        $this->success = false;
        $this->errors = [];
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
        ];
    }

    public function success(): void
    {
        $this->success = true;
    }

    public function error(string $errorCode): void
    {
        if (!in_array($errorCode, $this->errors)) {
            $this->errors[] = $errorCode;
        }
    }
}
