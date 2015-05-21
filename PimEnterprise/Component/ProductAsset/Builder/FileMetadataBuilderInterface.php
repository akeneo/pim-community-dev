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
 * Builder for FileMetadata
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface FileMetadataBuilderInterface
{
    /**
     * @param \SplFileInfo $file
     *
     * @return \PimEnterprise\Component\ProductAsset\Model\FileMetadata
     */
    public function build(\SplFileInfo $file);
}
