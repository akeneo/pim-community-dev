<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindReferenceEntityDetails implements FindReferenceEntityDetailsInterface
{
    /** @var ReferenceEntityDetails[] */
    private $results = [];

    public function save(ReferenceEntityDetails $referenceEntityDetails)
    {
        $key = $this->getKey($referenceEntityDetails->identifier);
        $this->results[$key] = $referenceEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): ?ReferenceEntityDetails {
        $key = $this->getKey($referenceEntityIdentifier);

        return $this->results[$key] ?? null;
    }

    private function getKey(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): string {
        return (string)$referenceEntityIdentifier;
    }
}
