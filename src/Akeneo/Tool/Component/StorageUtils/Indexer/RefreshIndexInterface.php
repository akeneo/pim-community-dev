<?php


namespace Akeneo\Tool\Component\StorageUtils\Indexer;


/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
interface RefreshIndexInterface
{
    public function refreshIndex(): void;
}