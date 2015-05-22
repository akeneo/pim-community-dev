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

    /** @var \DateTime */
    protected $modificationDatetime;

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
    public function getModificationDatetime()
    {
        return $this->modificationDatetime;
    }

    /**
     * {@inheritdoc}
     */
    public function setModificationDatetime(\DateTime $modificationDatetime)
    {
        $this->modificationDatetime = $modificationDatetime;

        return $this;
    }
}
