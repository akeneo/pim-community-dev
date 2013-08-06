<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

final class Events
{
    /**
     * Config Event Names
     */
    const NEW_ENTITY     = 'entity_config.new.entity';
    const NEW_FIELD      = 'entity_config.new.field';
    const PERSIST_CONFIG = 'entity_config.persist.config';
    const PRE_FLUSH      = 'entity_config.pre.flush';
    const ON_FLUSH       = 'entity_config.on.flush';
    const POST_FLUSH     = 'entity_config.post.flush';
}
