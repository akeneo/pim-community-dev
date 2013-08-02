<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

final class Events
{
    /**
     * Config Event Names
     */
    const NEW_CONFIG_MODEL = 'entity_config.new.config_model';
    const PERSIST_CONFIG   = 'entity_config.persist.config';
    const UPDATE_CONFIG    = 'entity_config.update.config';
    const REMOVE_CONFIG    = 'entity_config.remove.config';
}
