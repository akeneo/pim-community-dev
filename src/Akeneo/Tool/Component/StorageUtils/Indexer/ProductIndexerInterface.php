<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Indexer;

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
     * @param array $options
     */
    public function removeFromProductId(int $productId, array $options = []): void;

    /**
     * @param int[] $productIdentifiers
     * @param array $options
     */
    public function removeFromProductIds(array $productIdentifiers, array $options = []): void;
}
