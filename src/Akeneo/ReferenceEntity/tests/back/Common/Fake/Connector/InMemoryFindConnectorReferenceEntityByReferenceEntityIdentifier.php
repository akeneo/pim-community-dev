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

namespace Akeneo\ReferenceEntity\Common\Fake\Connector;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;

class InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier implements FindConnectorReferenceEntityByReferenceEntityIdentifierInterface
{
    /** @var ConnectorReferenceEntity[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ConnectorReferenceEntity $connectorReferenceEntity
    ): void {
        $this->results[(string) $referenceEntityIdentifier] = $connectorReferenceEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): ?ConnectorReferenceEntity {
        return $this->results[(string) $referenceEntityIdentifier] ?? null;
    }
}
