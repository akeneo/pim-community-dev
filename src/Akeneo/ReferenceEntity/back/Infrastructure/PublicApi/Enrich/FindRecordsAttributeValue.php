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

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;

class FindRecordsAttributeValue implements FindRecordsAttributeValueInterface
{
    public function __construct(
        private FindRecordDetailsInterface $findRecordDetails,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        string $referenceEntityCode,
        array $recordCodes,
        string $referenceEntityAttributeCode,
        ?string $channel = null,
        ?string $locale = null,
    ): array {
        $recordCodes = array_map(static fn (string $recordCode) => RecordCode::fromString($recordCode), $recordCodes);
        $recordsDetails = $this->findRecordDetails->findByCodes(
            ReferenceEntityIdentifier::fromString($referenceEntityCode),
            $recordCodes,
        );

        $results = [];

        foreach ($recordsDetails as $recordDetails) {
            $attributeValue = current(array_filter(
                $recordDetails->values,
                static fn (array $value) => $value['attribute']['code'] === $referenceEntityAttributeCode
                    && $value['channel'] === $channel
                    && $value['locale'] === $locale,
            ));

            $results[(string) $recordDetails->code] = $attributeValue['data'] ?? null;
        }

        return $results;
    }
}
