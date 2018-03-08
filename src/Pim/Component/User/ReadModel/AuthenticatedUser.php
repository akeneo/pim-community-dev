<?php

declare(strict_types=1);

namespace Pim\Component\User\ReadModel;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Read model (DTO) that represents the data needed to authenticated an user.
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
    /** @var string */
    private $email;

    /**
     * @param int    $id
     * @param string $username
     * @param string $password
     * @param array  $roles
     * @param bool   $enabled
     * @param string $salt
     * @param string $uiLocale
     * @param string $email
     */
    public function __construct(
        int $id,
        string $username,
        string $password,
        array $roles,
        bool $enabled,
        string $salt,
        string $uiLocale,
        string $email
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->salt = $salt;
        $this->uiLocale = $uiLocale;
        $this->email = $email;
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

    /**
     * @return bool
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked(): bool
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function Email(): string
    {
        return $this->email;
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
