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

use Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface;

/**
 * Builder for FileMetadata
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FileMetadataBuilder implements FileMetadataBuilderInterface
{
    /** @var string */
    protected $metadataClass;

    /** @var FileMetadataReaderFactoryInterface */
    protected $metaReaderFactory;

    /**
     * @param FileMetadataReaderFactoryInterface $metaReaderFactory
     * @param string                             $metadataClass
     */
    public function __construct(
        FileMetadataReaderFactoryInterface $metaReaderFactory,
        $metadataClass = 'PimEnterprise\Component\ProductAsset\Model\FileMetadata'
    ) {
        $this->metaReaderFactory = $metaReaderFactory;
        $this->metadataClass     = $metadataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(\SplFileInfo $file)
    {
        $metadataReader = $this->getMetadataReader($file);
        $metadataReader->all($file);
        $metadata = $metadataReader->getMetadata();

        $fileMetadata = new $this->metadataClass();
        $fileMetadata->setFileDatetime($metadata->get('exif.FILE.FileDateTime'));

        return $fileMetadata;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return \Akeneo\Component\FileMetadata\FileMetadataReader
     */
    protected function getMetadataReader(\SplFileInfo $file)
    {
        return $this->metaReaderFactory->create($file);
    }
}
