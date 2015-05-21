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
 * Implementation of the FileMetadataInterface
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FileMetadata implements FileMetadataInterface
{
    /** @var integer */
    protected $id;

    /** @var File */
    protected $file;

    /** @var string */
    protected $fileDatetime;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileDatetime()
    {
        return $this->fileDatetime;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileDatetime($fileDatetime)
    {
        $this->fileDatetime = $fileDatetime;

        return $this;
    }
}
