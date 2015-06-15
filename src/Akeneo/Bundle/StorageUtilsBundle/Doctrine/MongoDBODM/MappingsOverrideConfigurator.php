<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

/**
 * Configure the MongoDB mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class is largely based on the Sylius project's class
 * Sylius/Bundle/ResourceBundle/EventListener/LoadODMMetadataSubscriber.php
 *
 * Original authors are Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 * and Paweł Jędrzejewski <pjedrzejewski@sylius.pl>
 */
class MappingsOverrideConfigurator implements MappingsOverrideConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(ClassMetadata $metadata, array $mappingOverrides, $configuration)
    {
        if (!$metadata instanceof ClassMetadataInfo) {
            throw new \InvalidArgumentException(
                'This configurator only handles "Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo".'
            );
        }
        if (!$configuration instanceof Configuration) {
            throw new \InvalidArgumentException(
                'This configurator only handles "Doctrine\ODM\MongoDB\Configuration".'
            );
        }

        foreach ($mappingOverrides as $override) {
            if ($override['override'] === $metadata->getName()) {
                $metadata->isMappedSuperclass = false;
                $this->setAssociationMappings($metadata, $configuration);
            }
            if ($override['original'] === $metadata->getName()) {
                $metadata->isMappedSuperclass = true;
                $this->unsetAssociationMappings($metadata);
            }
        }

        return $metadata;
    }

    /**
     * Set the association mappings of a metadata.
     *
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     */
    protected function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration)
    {
        $supportedClasses = $configuration->getMetadataDriverImpl()->getAllClassNames();

        foreach (class_parents($metadata->getName()) as $parent) {
            if (in_array($parent, $supportedClasses)) {
                $parentMetadata = new MongoClassMetadata($parent);
                $configuration->getMetadataDriverImpl()->loadMetadataForClass($parent, $parentMetadata);

                foreach ($parentMetadata->associationMappings as $key => $value) {
                    if ($this->hasRelation($value['association'])) {
                        $metadata->associationMappings[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Unset the association mappings of a metadata.
     *
     * @param ClassMetadataInfo $metadata
     */
    protected function unsetAssociationMappings(ClassMetadataInfo $metadata)
    {
        foreach ($metadata->associationMappings as $key => $value) {
            if ($this->hasRelation($value['association'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected function hasRelation($type)
    {
        return in_array(
            $type,
            [
                ClassMetadataInfo::REFERENCE_ONE,
                ClassMetadataInfo::REFERENCE_MANY,
                ClassMetadataInfo::EMBED_ONE,
                ClassMetadataInfo::EMBED_MANY,
            ],
            true
        );
    }
}
