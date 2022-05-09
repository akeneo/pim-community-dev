<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain\Command;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogCommand
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $id,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        private string $name,
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
}
