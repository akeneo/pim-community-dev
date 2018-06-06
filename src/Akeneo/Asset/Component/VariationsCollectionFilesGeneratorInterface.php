<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component;

use Akeneo\Asset\Component\Model\VariationInterface;

/**
 * Generate the variation files from a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface VariationsCollectionFilesGeneratorInterface
{
    /**
     * @param VariationInterface[] $variations
     * @param bool                 $force      Process locked variations
     *
     * @return ProcessedItemList
     */
    public function generate(array $variations, $force = false);
}
