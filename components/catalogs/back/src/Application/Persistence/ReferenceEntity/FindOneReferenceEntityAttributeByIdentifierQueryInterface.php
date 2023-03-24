<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\ReferenceEntity;

use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\ReferenceEntityAttribute;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ReferenceEntityAttribute array{
 *      identifier: string,
 *      labels: array<string, string>,
 *      type: string,
 *      scopable: bool,
 *      localizable: bool
 * }
 */
interface FindOneReferenceEntityAttributeByIdentifierQueryInterface
{
    /**
     * @return ReferenceEntityAttribute|null
     */
    public function execute(string $identifier, string $locale = 'en_US'): ?array;
}
