<?php

namespace Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\Metadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class LoadMetadataSubscriber implements EventSubscriber
{
    /**
     * FQDN of the model
     * @var string
     */
    private $model;

    /**
     * FQDN of the epository
     * @var string
     */
    private $repository;

    /**
     * Mapping
     * @var array
     */
    private $mapping = [];

    public function __construct($model, array $mapping = [], $repository = null, $type = 'entity')
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->mapping = $mapping;
        $this->modelType = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        if ($this->model !== $classMetadata->getName()) {
            return;
        }

        if (null !== $this->repository) {
            $classMetadata->customRepositoryClassName = $this->repository;
        }

        foreach ($this->mapping as $type => $class) {
            if ($classMetadata->hasField($type)) {
                $classMetadata->fieldMappings[$type][$this->getTargetType()] = $class;
            }
        }
    }

    /**
     * Return the target type
     * For exemple for a document "targetDocument" will be returned
     *
     * @return string
     */
    private function getTargetType()
    {
        return sprintf('target%s', ucfirst($this->modelType));
    }
}
