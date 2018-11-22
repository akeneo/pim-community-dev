<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Asset\Component\Completeness;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface as BaseCompletenessRemoverInterface;

/**
* Completeness remover interface.
*
* It's not a {@link Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface}
* as its purpose is not to remove the completenesses objects that are given as argument, but
* instead to remove the completenesses that are linked to a given product, family or couple
* locale/channel.
*
* @author Julien Janvier (julien.janvier@akeneo.com)
*/
interface CompletenessRemoverInterface extends BaseCompletenessRemoverInterface
{
    /**
     * Remove completenesses for all products linked to an asset
     *
     * @param AssetInterface $asset
     */
    public function removeForAsset(AssetInterface $asset);
}
