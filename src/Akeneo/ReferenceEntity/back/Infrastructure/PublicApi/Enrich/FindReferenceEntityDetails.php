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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface as DomainFindReferenceEntityDetails;

class FindReferenceEntityDetails implements FindReferenceEntityDetailsInterface
{
    public function __construct(
        private DomainFindReferenceEntityDetails $findReferenceEntityDetails,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $referenceEntityCode): ?ReferenceEntityDetails
    {
        $referenceEntityDetails = $this->findReferenceEntityDetails->find(
            ReferenceEntityIdentifier::fromString($referenceEntityCode),
        );

        if (null === $referenceEntityDetails) {
            return null;
        }

        return ReferenceEntityDetails::fromDomain($referenceEntityDetails);
    }
}
