<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryCodes
{
    /**
     * @param UuidInterface[] $uuids
     * @return array<string, string[]> example:
     *  {
     *      "973ba04e-a035-41f4-86f4-2664730fc0ff": ["categoryA", "categoryB"],
     *      "0399ae56-8f80-4190-ad0f-28dd42772b83": ["categoryA"],
     *      ...
     *  }
     */
    public function fromProductUuids(array $uuids): array;
}
