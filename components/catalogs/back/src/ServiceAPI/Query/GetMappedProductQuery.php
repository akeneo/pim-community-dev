<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type MappedProduct array<string, string>
 *
 * @implements QueryInterface<MappedProduct>
 */
class GetMappedProductQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $catalogId,
        #[Assert\Uuid]
        private string $productUuid,
    ) {
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getProductUuid(): string
    {
        return $this->productUuid;
    }
}
