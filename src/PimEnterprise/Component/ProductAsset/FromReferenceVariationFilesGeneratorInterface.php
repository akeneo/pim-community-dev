<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Generate the variation files from a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface FromReferenceVariationFilesGeneratorInterface
{
    /**
     * @param ReferenceInterface $reference
     *
     * @return ProcessedItemList
     */
    public function generate(ReferenceInterface $reference);
}
