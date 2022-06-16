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
 * @codeCoverageIgnore
 */
class GetCatalogsByOwnerUsernameQuery implements QueryInterface
{
    public function __construct(
        private string $ownerUsername,
        #[Assert\Positive]
        private int $page,
        #[Assert\Range(min: 1, max: 100)]
        private int $limit,
    ) {
    }

    public function getOwnerUsername(): string
    {
        return $this->ownerUsername;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
