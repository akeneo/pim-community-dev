<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

class ActionMetadataProvider
{
    /**
     * @var AclAnnotationProvider
     */
    protected $annotationProvider;

    /**
     * Constructor
     *
     * @param AclAnnotationProvider $annotationProvider
     */
    public function __construct(AclAnnotationProvider $annotationProvider = null)
    {
        $this->annotationProvider = $annotationProvider;
    }

    /**
     * Gets metadata for all actions.
     *
     * @return ActionMetadata[]
     */
    public function getActions()
    {
        $result = array();
        foreach ($this->annotationProvider->getAnnotations('action') as $annotation) {
            $result[] = new ActionMetadata(
                $annotation->getId(),
                $annotation->getGroup(),
                $annotation->getLabel()
            );
        }

        return $result;
    }
}
