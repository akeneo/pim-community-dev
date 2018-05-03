<?php

namespace Akeneo\Tool\Component\StorageUtils\Indexer;

/**
 * Bulk indexer interface, provides a minimal contract to index many business objects in the search engine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkIndexerInterface
{
    /**
     * Index many objects
     *
     * @param array $objects The objects to index
     * @param array $options The saving options
     *
     * @throws \InvalidArgumentException
     */
    public function indexAll(array $objects, array $options = []);
}
