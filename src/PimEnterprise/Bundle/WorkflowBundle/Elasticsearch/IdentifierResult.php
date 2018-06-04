<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch;

use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

/**
 * Simple data holder for the results of an Elasticsearch search about products draft and product models draft.
 * The idea is to keep the identifier and its type correctly sorted.
 * Because we can have both a product and a product model with the same identifier.
 * Copied from Pim\Bundle\CatalogBundle\Elasticsearch\IdentifierResult
 *
 * @internal
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class IdentifierResult
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $type;

    public function __construct(string $identifier, string $type)
    {
        if ($type !== ProductDraft::class && $type !== ProductModelDraft::class) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Type of identifier result should be either "%s" or "%s". "%s" given',
                    ProductDraft::class,
                    ProductModelDraft::class,
                    $type
                )
            );
        }

        $this->identifier = (string) $identifier;
        $this->type = $type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isProductIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductDraft::class === $this->type;
    }

    public function isProductModelIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductModelDraft::class === $this->type;
    }
}
