<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Configure the ORM mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class is largely based on the Sylius project's class
 * Sylius/Bundle/ResourceBundle/EventListener/LoadORMMetadataSubscriber.php
 *
 * Original authors are Ivan Molchanov <ivan.molchanov@opensoftdev.ru>
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
                'This configurator only handles "Doctrine\ORM\Mapping\ClassMetadataInfo".'
            );
        }
        if (!$configuration instanceof Configuration) {
            throw new \InvalidArgumentException(
                'This configurator only handles "Doctrine\ORM\Configuration".'
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
                $parentMetadata = new OrmClassMetadata(
                    $parent,
                    $configuration->getNamingStrategy()
                );

                $configuration->getMetadataDriverImpl()->loadMetadataForClass($parent, $parentMetadata);

                foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                    if ($this->hasRelation($value['type'])) {
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
        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->hasRelation($value['type'])) {
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
                ClassMetadataInfo::MANY_TO_MANY,
                ClassMetadataInfo::MANY_TO_ONE,
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::ONE_TO_ONE,
            ],
            true
        );
    }
}
