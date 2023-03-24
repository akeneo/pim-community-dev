<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\ReferenceEntity;

use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\GetReferenceEntityAttributesQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetReferenceEntityAttributesQuery implements GetReferenceEntityAttributesQueryInterface
{
    public function execute(
        string $referenceEntityIdentifier,
        array $types = [],
        string $locale = 'en_US',
    ): array {
        // not supported in CE
        return [];
    }
}
