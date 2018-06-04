<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch;

use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

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
