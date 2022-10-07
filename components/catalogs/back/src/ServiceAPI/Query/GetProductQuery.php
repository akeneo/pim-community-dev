<?php

declare(strict_types=1);
namespace Akeneo\Catalogs\ServiceAPI\Query;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductValue array{
 *      scope: string|null,
 *      locale: string|null,
 *      data: mixed,
 * }
 *
 * @phpstan-type Product array{
 *      uuid: string,
 *      enabled: boolean,
 *      family: string,
 *      categories: array<string>,
 *      groups: array<string>,
 *      parent: string|null,
 *      values: array<string, ProductValue>,
 *      associations: array<string, array{groups: array<string>, products: array<string>, product_models: array<string>}>,
 *      quantified_associations: array<string, array{products: array<string>, product_models: array<string>}>,
 *      created: string,
 *      updated: string,
 * }
 *
 * @implements QueryInterface<Product>
 * @codeCoverageIgnore
 */
class GetProductQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $catalogId,
        #[Assert\NotBlank]
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
