<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsCatalogValid implements IsCatalogValidInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Catalog $catalog): bool
    {
        $violations = $this->validator->validate($catalog);

        return \count($violations) === 0;
    }
}
