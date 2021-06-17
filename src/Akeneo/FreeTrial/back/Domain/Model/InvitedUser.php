<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Model;

use Akeneo\FreeTrial\Domain\Exception\InvalidEmailException;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InvitedUser
{
    private string $email;

    private InvitedUserStatus $status;

    public function __construct(string $email, InvitedUserStatus $status)
    {
        try {
            Assert::email($email);
        } catch (\Exception $e) {
            throw new InvalidEmailException();
        }

        $this->email = $email;
        $this->status = $status;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): InvitedUserStatus
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'status' => (string) $this->status,
        ];
    }
}
