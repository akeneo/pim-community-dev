<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements QueryInterface<object>
 * @codeCoverageIgnore
 */
final class GetProductMappingSchemaQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $catalogId,
    ) {
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }
}
