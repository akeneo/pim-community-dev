<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindRecordsLabelTranslations implements FindRecordsLabelTranslationsInterface
{
    public function __construct(
        private FindRecordLabelsByCodesInterface $findRecordLabelsByCodes
    ) {
    }

    public function find(string $referenceEntityCode, array $recordCodes, $locale): array
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityCode);
        $labelCollections = $this->findRecordLabelsByCodes->find($referenceEntityIdentifier, $recordCodes);

        $result = [];

        /** @var LabelCollection $labelCollection */
        foreach ($labelCollections as $assetCode => $labelCollection) {
            $result[$assetCode] = $labelCollection->getLabel($locale);
        }

        return $result;
    }
}
