<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager as BaseMediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Gaufrette\Filesystem;

/**
 * Enterprise edition media manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MediaManager extends BaseMediaManager
{
    /** @var MediaFactory */
    protected $factory;

    /**
     * Constructor
     *
     * @param Filesystem   $filesystem
     * @param string       $uploadDirectory
     * @param MediaFactory $factory
     */
    public function __construct(
        Filesystem $filesystem,
        $uploadDirectory,
        MediaFactory $factory
    ) {
        parent::__construct($filesystem, $uploadDirectory);

        $this->factory = $factory;
    }

    /**
     * Create a media and load file information
     *
     * @param string $filename
     *
     * @return \Pim\Bundle\CatalogBundle\Factory\Media
     *
     * @see PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue\MediaDenormalizer
     * @throws \InvalidArgumentException When file does not exist
     */
    public function createFromFilename($filename)
    {
        $filePath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!$this->filesystem->has($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $filePath));
        }
        $media = $this->factory->createMedia();
        $media->setOriginalFilename($filename);
        $media->setFilename($filename);
        $media->setFilePath($filePath);
        $media->setMimeType($this->filesystem->mimeType($filename));

        return $media;
    }

    /**
     * {@inheritdoc}
     *
     * Media are never automatically removed from file system in community edition.
     * That way we are able to restore them.
     */
    protected function delete(AbstractProductMedia $media)
    {
        return;
    }
}
