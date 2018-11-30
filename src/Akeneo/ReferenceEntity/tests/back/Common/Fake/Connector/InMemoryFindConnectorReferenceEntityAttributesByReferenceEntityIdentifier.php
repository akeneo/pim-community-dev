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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface;

class InMemoryFindConnectorReferenceEntityAttributesByReferenceEntityIdentifier implements FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface
{
    /** @var ConnectorAttribute[] */
    private $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ConnectorAttribute $connectorAttribute
    ): void {
        $this->attributes[(string) $referenceEntityIdentifier][] = $connectorAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        return $this->attributes[(string) $referenceEntityIdentifier] ?? [];
    }
}
