<?php

namespace Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
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
 * Original authors are Ivannis Suárez Jérez <ivannis.suarez@gmail.com> and Paweł Jędrzejewski <pjedrzejewski@sylius.pl>
 *
 * TODO: spec it
 */
class ConfigureMongoMappingsSubscriber implements EventSubscriber
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
            $configuration = $eventArgs->getDocumentManager()->getConfiguration();
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
    protected function setAssociationMappings(ClassMetadataInfo $metadata, $configuration)
    {
        foreach (class_parents($metadata->getName()) as $parent) {
            $parentMetadata = new ClassMetadata($parent);
            if (in_array($parent, $configuration->getMetadataDriverImpl()->getAllClassNames())) {
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
