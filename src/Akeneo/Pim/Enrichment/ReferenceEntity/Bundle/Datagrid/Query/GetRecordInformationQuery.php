<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Query;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;

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

    public function execute(string $referenceEntityIdentifier, string $recordCode): RecordInformation
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

        return new RecordInformation($referenceEntityIdentifier, $recordCode, $recordDetails->labels->normalize());
    }
}
