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

use PimEnterprise\Component\ProductAsset\Exception\AlreadyRegisteredMetadataBuilderException;
use PimEnterprise\Component\ProductAsset\Exception\NonRegisteredMetadataBuilderException;

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

    /**
     * @return MetadataBuilderInterface[]
     */
    public function all()
    {
        $builders = [];

        foreach ($this->builders as $builder) {
            $builders[] = $builder['service'];
        }

        return $builders;
    }

    /**
     * @param MetadataBuilderInterface $builder
     * @param array                    $mimeTypes
     * @param string                   $alias
     *
     * @throws AlreadyRegisteredMetadataBuilderException
     *
     * @return MetadataBuilderRegistry
     */
    public function add(MetadataBuilderInterface $builder, array $mimeTypes, $alias)
    {
        if ($this->has($alias)) {
            throw new AlreadyRegisteredMetadataBuilderException(sprintf('Metadata builder "%s" already registered.', $alias));
        }

        $this->builders[$alias] = [
            'service'   => $builder,
            'mimeTypes' => $mimeTypes
        ];

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
            return $this->builders[$alias]['service'];
        }

        throw new NonRegisteredMetadataBuilderException(sprintf('No "%s" metadata builder found.', $alias));
    }

    /**
     * @param string $mimeType
     *
     * @return MetadataBuilderInterface[]
     */
    public function allByMimeType($mimeType)
    {
        $matchingBuilders= [];

        foreach ($this->builders as $builder) {
            if (in_array($mimeType, $builder['mimeTypes'])) {
                $matchingBuilders[] = $builder['service'];
            }
        }

        return $matchingBuilders;
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
