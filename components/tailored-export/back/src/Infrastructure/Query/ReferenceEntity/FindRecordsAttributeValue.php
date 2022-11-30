<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsAttributeValueInterface as FindRecordsAttributeValueServiceApi;

class FindRecordsAttributeValue implements FindRecordsAttributeValueInterface
{
    public function __construct(
        private FindRecordsAttributeValueServiceApi $findRecordsAttributeValue,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        string $referenceEntityCode,
        array $recordCodes,
        string $referenceEntityAttributeIdentifier,
        ?string $channel = null,
        ?string $locale = null,
    ): array {
        return $this->findRecordsAttributeValue->find(
            $referenceEntityCode,
            $recordCodes,
            $referenceEntityAttributeIdentifier,
            $channel,
            $locale,
        );
    }
}
