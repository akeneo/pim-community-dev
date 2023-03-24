<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\ReferenceEntity;

use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ReferenceEntityAttribute from FindOneReferenceEntityAttributeByIdentifierQueryInterface
 */
interface GetReferenceEntityAttributesQueryInterface
{
    /**
     * @param array<string> $types
     *
     * @return array<ReferenceEntityAttribute>
     */
    public function execute(
        string $referenceEntityIdentifier,
        array $types = [],
        string $locale = 'en_US',
    ): array;
}
