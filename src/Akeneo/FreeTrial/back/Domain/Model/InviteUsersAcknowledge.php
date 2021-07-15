<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
