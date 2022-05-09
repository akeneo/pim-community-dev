<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain\Query;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements Query<Catalog>
 */
final class GetCatalogQuery implements Query
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
