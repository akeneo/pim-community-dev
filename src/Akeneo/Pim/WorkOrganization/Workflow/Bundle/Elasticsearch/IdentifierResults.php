<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

/**
 * Simple collection of {@see IdentifierResult}.
 *
 * Allows to retrieve the results matching products draft or matching product models draft.
 *
 * @internal
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class IdentifierResults
{
    /** @var IdentifierResult[] */
    private $identifierResults = [];

    public function add(string $identifier, string $type)
    {
        $this->identifierResults[] = new IdentifierResult($identifier, $type);
    }

    public function getProductIdentifiers(): array
    {
        return $this->getIdentifiersByType(ProductDraft::class);
    }

    public function getProductModelIdentifiers(): array
    {
        return $this->getIdentifiersByType(ProductModelDraft::class);
    }

    public function all(): array
    {
        return $this->identifierResults;
    }

    public function isEmpty(): bool
    {
        return empty($this->identifierResults);
    }

    private function getIdentifiersByType(string $type): array
    {
        $identifiers = [];

        foreach ($this->identifierResults as $identifierResult) {
            if ($type === $identifierResult->getType()) {
                $identifiers[] = $identifierResult->getIdentifier();
            }
        }

        return $identifiers;
    }
}
