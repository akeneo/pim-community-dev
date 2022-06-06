<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Command;

use Akeneo\Catalogs\Domain\Validation\GetOwnerIdInterface;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

#[MaxNumberOfCatalogsPerUser]
final class CreateCatalogCommand implements CommandInterface, GetOwnerIdInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $id,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        private string $name,
        private int $ownerId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }
}
