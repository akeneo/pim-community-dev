<?php

namespace Akeneo\Bundle\StorageUtilsBundle\EventListener\MongoDBODM;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * Mechanism to overwrite entity interfaces specified as fields targets.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see Doctrine\ODM\MongoDB\Tools\ResolveTargetDocumentListener
 */
class ResolveTargetEntityListener
{
    /** @var array */
    protected $targetEntities = [];

    /**
     * Add a target-document class name to resolve to a new class name.
     *
     * @param string $originalEntity
     * @param string $newEntity
     * @param array  $mapping
     */
    public function addResolveTargetEntity($originalEntity, $newEntity, array $mapping)
    {
        $mapping['targetEntity'] = ltrim($newEntity, "\\");
        $this->targetEntities[ltrim($originalEntity, "\\")] = $mapping;
    }

    /**
     * Process event and resolve new target document names.
     *
     * @param LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $cm = $args->getClassMetadata();
        foreach ($cm->fieldMappings as $mapping) {
            if (isset($mapping['targetEntity']) && isset($this->targetEntities[$mapping['targetEntity']])) {
                $this->remapField($cm, $mapping);
            }
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array         $mapping
     */
    protected function remapField(ClassMetadata $classMetadata, array $mapping)
    {
        $newMapping = $this->targetEntities[$mapping['targetEntity']];
        $newMapping = array_replace_recursive($mapping, $newMapping);
        $newMapping['fieldName'] = $mapping['fieldName'];

        // clear reference case of duplicate exception
        unset($classMetadata->fieldMappings[$mapping['fieldName']]);
        $classMetadata->fieldMappings[$mapping['fieldName']] = $newMapping;
    }
}
