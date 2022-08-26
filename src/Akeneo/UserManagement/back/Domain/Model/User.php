<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\Domain\Model;

final class User
{
    private function __construct(
        private int $id,
        private string $email,
        private string $username,
        private string $userType,
        private ?string $firstname,
        private ?string $lastname,
        private ?string $middleName,
        private ?string $nameSuffix,
        private ?string $image
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getNameSuffix(): ?string
    {
        return $this->nameSuffix;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public static function createFromDatabase(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['email'],
            $data['username'],
            $data['user_type'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['name_suffix'],
            $data['image']
        );
    }
}
