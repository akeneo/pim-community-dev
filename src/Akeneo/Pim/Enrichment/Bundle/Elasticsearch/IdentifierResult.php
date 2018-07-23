<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * Simple data holder for the results of an Elasticsearch search about products and product models.
 * The idea is to keep the identifier and its type correctly sorted.
 * Because we can have both a product and a product model with the same identifier.
 *
 * @internal
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierResult
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $type;

    /**
     * @param string $identifier
     * @param string $type
     */
    public function __construct(string $identifier, string $type)
    {
        if ($type !== ProductInterface::class && $type !== ProductModelInterface::class) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Type of identifier result should be either "%s" or "%s". "%s" given',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    $type
                )
            );
        }

        $this->identifier = (string) $identifier;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isProductIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductInterface::class === $this->type;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isProductModelIdentifierEquals(string $identifier): bool
    {
        return $identifier === $this->identifier && ProductModelInterface::class === $this->type;
    }
}
