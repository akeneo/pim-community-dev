<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

class ActionMetadataProvider
{
    /**
     * Gets metadata for all actions.
     *
     * @return ActionMetadata[]
     */
    public function getActions()
    {
        // @todo not implemented yet
        return array(
            new ActionMetadata('DemoAction1', '', 'Demo Action 1'),
            new ActionMetadata('DemoAction2', '', 'Demo Action 2'),
        );
    }
}
