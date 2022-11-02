<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Command;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @codeCoverageIgnore
 */
final class DeleteProductMappingSchemaCommand implements CommandInterface
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
