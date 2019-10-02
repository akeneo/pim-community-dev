<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductIndexerInterface
{
    /**
     * @param string $productIdentifier
     * @param array  $options
     *
     * @throws ObjectNotFoundException if the identifier is unknown
     */
    public function indexFromProductIdentifier(string $productIdentifier, array $options = []): void;

    /**
     * @param string[] $productIdentifiers
     * @param array    $options
     *
     * @throws ObjectNotFoundException if one of the identifier is unknown
     */
    public function indexFromProductIdentifiers(array $productIdentifiers, array $options = []): void;

    /**
     * @param int   $productId
     */
    public function removeFromProductId(int $productId): void;

    /**
     * @param int[] $productIds
     */
    public function removeFromProductIds(array $productIds): void;
}
