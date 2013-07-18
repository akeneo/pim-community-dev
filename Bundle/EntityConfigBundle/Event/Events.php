<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

final class Events
{
    const CREATE_ENTITY_CONFIG = 'entity_config.new.entity';
    const CREATE_FIELD_CONFIG  = 'entity_config.new.field';

    const UPDATE_ENTITY_CONFIG = 'entity_config.update.entity';
    const UPDATE_FIELD_CONFIG  = 'entity_config.update.field';
}
