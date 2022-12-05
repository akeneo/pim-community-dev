<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
 */
interface SearchAttributesQueryInterface
{
    /**
     * @param array<string> $types
     * @return array<Attribute>
     */
    public function execute(?string $search = null, int $page = 1, int $limit = 20, array $types = []): array;
}
