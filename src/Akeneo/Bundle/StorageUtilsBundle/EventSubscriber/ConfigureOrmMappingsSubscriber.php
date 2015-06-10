<?php

namespace Akeneo\Bundle\StorageUtilsBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
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
 * Original authors are Ivan Molchanov <ivan.molchanov@opensoftdev.ru> and Paweł Jędrzejewski <pjedrzejewski@sylius.pl>
 *
 * TODO: spec it
 */
class ConfigureOrmMappingsSubscriber implements EventSubscriber
{
    /** @var array */
    protected $mappingOverrides;

    /**
     * Constructor
     *
     * @param array $mappingOverrides
     */
    public function __construct(array $mappingOverrides)
    {
        $this->mappingOverrides = $mappingOverrides;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata instanceof ClassMetadataInfo) {
            $configuration = $eventArgs->getEntityManager()->getConfiguration();
            $this->configureMappings($metadata, $configuration);
        }
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     */
    protected function configureMappings(ClassMetadataInfo $metadata, Configuration $configuration)
    {
        foreach ($this->mappingOverrides as $override) {
            if ($override['override'] === $metadata->getName()) {
                $metadata->isMappedSuperclass = false;
                $this->setAssociationMappings($metadata, $configuration);
            }
            if ($override['original'] === $metadata->getName()) {
                $metadata->isMappedSuperclass = true;
                $this->unsetAssociationMappings($metadata);
            }
        }
    }

    /**
     * Set the association mappings of a metadata.
     *
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     */
    protected function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration)
    {
        foreach (class_parents($metadata->getName()) as $parent) {
            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );
            if (in_array($parent, $configuration->getMetadataDriverImpl()->getAllClassNames())) {
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
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::ONE_TO_ONE,
            ],
            true
        );
    }
}
