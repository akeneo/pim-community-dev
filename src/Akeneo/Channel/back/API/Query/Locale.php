<?php

namespace Akeneo\Channel\API\Query;

use OpenApi\Attributes as OA;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// generate a schema for this class following example in Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier
#[OA\Schema(
    schema: 'Locale',
    properties: [
        new OA\Property(
            property: 'code',
            type: 'string',
        ),
        new OA\Property(
            property: 'enabled',
            type: 'boolean',
        ),
    ],
)]
final class Locale
{
    public function __construct(
        private string $code,
        private bool $isActivated
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }
}
