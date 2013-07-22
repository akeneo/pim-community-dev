Config Provider
====================

Config provider should be specified in services.yml
and described in entity_config.yml (see configuration.md)

For example:

    oro_entity.config.audit_config_provider:
        tags:
            - { name: oro_entity_config.provider, scope: audit }

Usage in code (any Bundle):

* $entity -> Doctrine entity instance

        /** @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider $entityAuditProvider */
        $entityAuditProvider = $this->get('oro_entity.config.audit_config_provider');

ConfigProvider methods:

  hasConfig({Entity class name}) : checks if entity has config
  getConfig({Entity class name}) : return configuration ( EntityConfig(AbstractConfig) instance )

  hasFieldConfig({Entity class name}, {Field code}) : checks if field of entity has config
  getFieldConfig({Entity class name}, {Field code}) : return configuration for specified field ( FieldConfig(AbstractConfig) instance )

  AbstractConfig->is({parameter})  : check if parameter exists or equal to TRUE, return boolean
  AbstractConfig->has({parameter}) : check if parameter exists, return boolean

  AbstractConfig->get({parameter}, {strict = FALSE}) : return parameters
    - if strict == TRUE and parameters NOT exists will be Exception
    - if strict == FALSE and parameters NOT exists will return NULL

  AbstractConfig->set({parameter}, {value}) : set parameter and return AbstractConfig

Simple usage example:
        if ($entityAuditProvider->hasConfig(get_class($entity))) {
            $audit_enabled = $entityAuditProvider->getConfig(get_class($entity))->is('auditable');
        }
