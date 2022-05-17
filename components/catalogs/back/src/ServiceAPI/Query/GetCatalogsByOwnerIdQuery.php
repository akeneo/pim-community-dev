<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements QueryInterface<array<Catalog>>
 */
class GetCatalogsByOwnerIdQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        private int $ownerId,
        #[Assert\PositiveOrZero]
        private int $offset,
        #[Assert\PositiveOrZero]
        private int $limit,
    ) {
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
