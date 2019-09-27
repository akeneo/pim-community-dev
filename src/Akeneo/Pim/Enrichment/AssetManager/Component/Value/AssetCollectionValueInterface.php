<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product value interface for a collection of asset family
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface AssetCollectionValueInterface extends ValueInterface
{
    /**
     * @return AssetCode[]
     */
    public function getData(): ?array;
}
