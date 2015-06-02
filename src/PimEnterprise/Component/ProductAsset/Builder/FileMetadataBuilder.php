<?php

/**
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
class FileMetadataBuilder implements MetadataBuilderInterface
{
    /** @var string */
    protected $metadataClass;

    /**
     * @param string $metadataClass
     */
    public function __construct($metadataClass = 'PimEnterprise\Component\ProductAsset\Model\FileMetadata')
    {
        $this->metadataClass = $metadataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(\SplFileInfo $file)
    {
        $fileMetadata = new $this->metadataClass();
        $fileMetadata->setModificationDatetime(new \DateTime(sprintf('@%s', $file->getMTime())));

        return $fileMetadata;
    }
}
