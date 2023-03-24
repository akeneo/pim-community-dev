<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\ReferenceEntity;

use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneReferenceEntityAttributeByIdentifierQuery implements FindOneReferenceEntityAttributeByIdentifierQueryInterface
{
    public function execute(string $identifier, string $locale = 'en_US'): ?array
    {
        // not supported in CE
        return null;
    }
}
