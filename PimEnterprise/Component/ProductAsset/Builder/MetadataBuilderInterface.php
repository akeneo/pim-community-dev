<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

/**
 * Metadata builder
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface MetadataBuilderInterface
{
    /**
     * @param \SplFileInfo $file
     *
     * @return \PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface
     */
    public function build(\SplFileInfo $file);
}
