<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Variation file generator interface.
 *
 * Generate the variation files, store them in the filesystem and link them to the reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface VariationFileGeneratorInterface
{
    /**
     * @param VariationInterface $variation
     */
    public function generate(VariationInterface $variation);
}
