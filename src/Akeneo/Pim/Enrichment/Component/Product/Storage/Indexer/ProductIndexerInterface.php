<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Ramsey\Uuid\UuidInterface;

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
     * @param UuidInterface[] $productUuids
     */
    public function removeFromProductUuids(array $productUuids): void;
}
