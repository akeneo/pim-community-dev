<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Query;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;

/**
 * This query acts as an anti corruption layer.
 *
 * It depends on a service defined in another bounded context to create its own results.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetRecordInformationQuery implements GetRecordInformationQueryInterface
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetails;

    public function __construct(FindRecordDetailsInterface $findRecordDetails)
    {
        $this->findRecordDetails = $findRecordDetails;
    }

    public function fetch(string $referenceEntityIdentifier, string $recordCode): RecordInformation
    {
        $recordDetails = ($this->findRecordDetails)(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($recordCode)
        );

        if (null === $recordDetails) {
            throw new \LogicException(
                sprintf(
                    'There was no information to fetch for reference entity "%s" and record code "%s"',
                    $referenceEntityIdentifier, $recordCode
                )
            );
        }

        $recordInformation = new RecordInformation();
        $recordInformation->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordInformation->code = $recordCode;
        $recordInformation->labels = $recordDetails->labels->normalize();

        return $recordInformation;
    }
}
