<?php

declare(strict_types=1);

namespace Pim\Component\User\ReadModel;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Object that represents the data needed to authenticated an user.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticatedUser implements AdvancedUserInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var array */
    private $roles;

    /** @var bool */
    private $enabled;

    /** @var string */
    private $salt;

    /** @var string */
    private $uiLocale;

    /**
     * @param int    $id
     * @param string $username
     * @param string $password
     * @param array  $roles
     * @param bool   $enabled
     * @param string $salt
     * @param string $uiLocale
     */
    public function __construct(
        int $id,
        string $username,
        string $password,
        array $roles,
        bool $enabled,
        string $salt,
        string $uiLocale
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->salt = $salt;
        $this->uiLocale = $uiLocale;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return array_map(
            function ($role) {
                return $role['role'];
            },
            $this->roles
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @return string
     */
    public function getUiLocale(): string
    {
        return $this->uiLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
    }

    public function isAccountNonExpired(): bool
    {
        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return $this->enabled;
    }

    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->password,
                $this->salt,
                $this->username,
                $this->enabled,
                $this->id,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->id
            ) = unserialize($serialized);
    }
}
