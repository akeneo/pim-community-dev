<?php

namespace Akeneo\Tool\Component\StorageUtils\Indexer;

/**
 * Interface ProductIndexerInterface
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductIndexerInterface
{
    /**
     * @param string $productIdentifier
     * @param array  $options
     */
    public function indexFromProductIdentifier(string $productIdentifier, array $options = []);

    /**
     * @param array $productIdentifiers
     * @param array $options
     */
    public function indexFromProductIdentifiers(array $productIdentifiers, array $options = []);

    /**
     * @param string $productIdentifier
     * @param array  $options
     */
    public function removeFromProductIdentifier(string $productIdentifier, array $options = []);

    /**
     * @param array $productIdentifiers
     * @param array $options
     */
    public function removeManyFromProductIdentifiers(array $productIdentifiers, array $options = []);
}
