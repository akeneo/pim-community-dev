<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class VariationHasBeenCreated extends Event
{
    /** @var string Event triggered after the creation of an asset variation. */
    const VARIATION_HAS_BEEN_CREATED = 'pimee_product_asset.variation_has_been_created';

    /**
     * @var AssetInterface
     */
    private $asset;

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    public function getAsset(): AssetInterface
    {
        return $this->asset;
    }
}
