<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * Simple collection of {@see IdentifierResult}.
 *
 * Allows to retrieve the results matching products or matching product models.
 *
 * @internal
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierResults
{
    /** @var IdentifierResult[] */
    private $identifierResults = [];

    /**
     * @param string $identifier
     * @param string $type
     */
    public function add(string $identifier, string $type)
    {
        $this->identifierResults[] = new IdentifierResult($identifier, $type);
    }

    /**
     * Returns the identifier list of product models. The sort is maintained among products.
     *
     * @return string[] our identifiers are string only
     */
    public function getProductIdentifiers(): array
    {
        return $this->getIdentifiersByType(ProductInterface::class);
    }

    /**
     * Returns the identifier list of product models. The sort is maintained among product models.
     *
     * @return string[] our identifiers are string only
     */
    public function getProductModelIdentifiers(): array
    {
        return $this->getIdentifiersByType(ProductModelInterface::class);
    }

    /**
     * @return IdentifierResult[]
     */
    public function all(): array
    {
        return $this->identifierResults;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->identifierResults);
    }

    /**
     * @param string $type
     *
     * @return string[] our identifiers are string only
     */
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
