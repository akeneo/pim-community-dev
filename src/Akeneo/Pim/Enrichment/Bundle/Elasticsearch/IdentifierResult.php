<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Ramsey\Uuid\Uuid;

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
    private ?string $identifier;
    private string $id;
    private string $type;

    public function __construct(?string $identifier, string $type, string $id)
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

        if ($type === ProductInterface::class && !Uuid::isValid(\str_replace('product_', '', $id))) {
            throw new \InvalidArgumentException(\sprintf("Product has an invalid uuid : %s", $id));
        }

        if ($type === ProductModelInterface::class && null === $identifier) {
            throw new \InvalidArgumentException('A product model should have an identifier defined');
        }

        $this->identifier = $identifier;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return ?string
     */
    public function getIdentifier(): ?string
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

    public function getId(): string
    {
        return $this->id;
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
