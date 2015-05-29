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

use PimEnterprise\Component\ProductAsset\Exception\AlreadyRegisteredMetadataBuilderException;
use PimEnterprise\Component\ProductAsset\Exception\NonRegisteredMetadataBuilderException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Registry for Metadata builders.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class MetadataBuilderRegistry
{
    /** @var MetadataBuilderInterface[] */
    protected $builders = [];

    /** @var array */
    protected $imageMimeTypes = ['image/jpeg', 'image/tiff', 'image/png'];

    /**
     * @return MetadataBuilderInterface[]
     */
    public function all()
    {
        return $this->builders;
    }

    /**
     * @param MetadataBuilderInterface $builder
     * @param string                   $alias
     *
     * @throws AlreadyRegisteredMetadataBuilderException
     *
     * @return MetadataBuilderRegistry
     */
    public function register(MetadataBuilderInterface $builder, $alias)
    {
        if ($this->has($alias)) {
            throw new AlreadyRegisteredMetadataBuilderException(sprintf('Metadata builder "%s" already registered.', $alias));
        }

        $this->builders[$alias] = $builder;

        return $this;
    }

    /**
     * @param string $alias
     *
     * @throws NonRegisteredMetadataBuilderException
     *
     * @return MetadataBuilderInterface
     */
    public function get($alias)
    {
        if ($this->has($alias)) {
            return $this->builders[$alias];
        }

        throw new NonRegisteredMetadataBuilderException(sprintf('No "%s" metadata builder found.', $alias));
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return MetadataBuilderInterface
     */
    public function getByFile(\SplFileInfo $file)
    {
        $mimeType = MimeTypeGuesser::getInstance()->guess($file->getPathname());

        if (in_array($mimeType, $this->imageMimeTypes)) {
            return $this->get('pimee_product_asset_image_metadata_builder');
        }

        return $this->get('pimee_product_asset_file_metadata_builder');
    }

    /**
     * @param string $alias
     *
     * @return bool
     */
    public function has($alias)
    {
        return isset($this->builders[$alias]);
    }
}
