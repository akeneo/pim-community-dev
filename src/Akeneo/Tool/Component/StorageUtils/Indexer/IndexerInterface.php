<?php

namespace Akeneo\Tool\Component\StorageUtils\Indexer;

/**
 * Indexer interface, provides a minimal contract to index a single business object in the search engine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IndexerInterface
{
    /**
     * Index a single object
     *
     * @param mixed $object  The object to index
     * @param array $options The saving options
     *
     * @throws \InvalidArgumentException
     */
    public function index($object, array $options = []);
}
