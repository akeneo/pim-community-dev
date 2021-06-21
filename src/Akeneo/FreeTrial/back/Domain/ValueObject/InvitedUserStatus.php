<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\ValueObject;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvitedUserStatus
{
    public const ACTIVE = 'active';
    public const INVITED = 'invited';

    private string $status;

    private function __construct(string $status)
    {
        $this->status = $status;
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function invited(): self
    {
        return new self(self::INVITED);
    }

    public static function fromString(string $status): self
    {
        if ($status !== self::ACTIVE && $status !== self::INVITED) {
            throw new \InvalidArgumentException('Status : ' . $status . ' is not valid for InvitedUser status.');
        }

        return new self($status);
    }

    public function __toString()
    {
        return $this->status;
    }
}