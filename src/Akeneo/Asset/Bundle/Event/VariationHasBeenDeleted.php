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

namespace Akeneo\Asset\Bundle\Event;

use Akeneo\Asset\Component\Model\AssetInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class VariationHasBeenDeleted extends Event
{
    /** @var string Event triggered after the deletion of an asset variation. */
    const VARIATION_HAS_BEEN_DELETED = 'pimee_product_asset.variation_has_been_deleted';

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
