<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ACL\Domain;

use Webmozart\Assert\Assert;

/**
 * Represents the user identifier of a logged in user.
 *
 * Do not use this object as part of your application or domain code, it's usage is strictly for information passing.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIdentifier
{
    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier);
        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function stringValue(): string
    {
        return $this->identifier;
    }
}

