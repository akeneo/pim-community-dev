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

use Akeneo\Component\FileStorage\Model\File;
use Akeneo\Component\FileStorage\Model\FileInterface;

/**
 * The FileMetadataInterface stores basic metadata for a file.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface FileMetadataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return File
     */
    public function getFile();

    /**
     * @param FileInterface $file
     *
     * @return FileMetadataInterface
     */
    public function setFile(FileInterface $file);

    /**
     * @return \DateTime
     */
    public function getModificationDatetime();

    /**
     * @param \DateTime $fileDatetime
     *
     * @return FileMetadataInterface
     */
    public function setModificationDatetime(\DateTime $fileDatetime);
}
