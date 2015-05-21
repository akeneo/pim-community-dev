<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

/**
 * The FileMetadataInterface stores basic metadata for a file.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface FileMetadataInterface
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getFileDatetime();

    /**
     * @param string $fileDatetime
     *
     * @return FileMetadataInterface
     */
    public function setFileDatetime($fileDatetime);
}
