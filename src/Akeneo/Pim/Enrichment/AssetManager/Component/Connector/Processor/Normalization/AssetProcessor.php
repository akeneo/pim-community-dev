<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Normalization;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Webmozart\Assert\Assert;

class AssetProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($asset): array
    {
        Assert::isInstanceOf($asset, Asset::class);

        return $asset->normalize();
    }
}
