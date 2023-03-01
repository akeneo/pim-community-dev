<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindIdentifier
{
    public function fromUuid(string $uuid): null|string;

    /**
     * @param string[] $uuids
     * @return array<string, string>
     */
    public function fromUuids(array $uuids): array;
}
