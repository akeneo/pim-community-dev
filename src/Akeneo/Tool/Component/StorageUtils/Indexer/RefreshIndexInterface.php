<?php


namespace Akeneo\Tool\Component\StorageUtils\Indexer;


/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RefreshIndexInterface
{
    public function refreshIndex(): void;
}
