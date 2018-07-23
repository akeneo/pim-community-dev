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
 * Simple data holder for the results of an Elasticsearch search about products draft and product models draft.
 * The idea is to keep the identifier and its type correctly sorted.
 * Because we can have both a product draft and a product model draft with the same identifier.
 * Copied from Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult
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

    public function isProductDraftIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductDraft::class === $this->type;
    }

    public function isProductModelDraftIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductModelDraft::class === $this->type;
    }
}
