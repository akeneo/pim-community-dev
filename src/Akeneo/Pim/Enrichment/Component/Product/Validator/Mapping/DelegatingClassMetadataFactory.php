<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping;

use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DelegatingClassMetadataFactory implements MetadataFactoryInterface
{
    /** @var array */
    protected $factories = [];

    /**
     * Register a metadata factory
     *
     * @param MetadataFactoryInterface $factory
     */
    public function addMetadataFactory(MetadataFactoryInterface $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        foreach ($this->factories as $factory) {
            if ($factory->hasMetadataFor($value)) {
                return $factory->getMetadataFor($value);
            }
        }

        throw new NoSuchMetadataException();
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        foreach ($this->factories as $factory) {
            if ($factory->hasMetadataFor($value)) {
                return true;
            }
        }

        return false;
    }
}
