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
 * Builder for ImageMetadata
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface ImageMetadataBuilderInterface
{
    /**
     * Builds an ImageMetadata with the given $file.
     *
     * @param \SplFileInfo $file
     *
     * @return \PimEnterprise\Component\ProductAsset\Model\ImageMetadataInterface
     */
    public function build(\SplFileInfo $file);
}
