Config(yml) Example
====================

Configuration YAML should be placed in [BundleName]\Resources\config\entity_config.yml

oro_entity_config:
    entity:                                                         # scope name
        entity:                                                     # config block for Entity instance

            form:                                                   # config block for Entity form ( FormBundle )
                block_config:                                       #
                    entity:                                         # block name
                        priority:           20                      # ability to sort block(s)
                        title:              'Entity Config'         # form block title
                        subblocks:                                  # subblock(s) configuration
                            base:           ~

            items:                                                  # config block for Entity properties

                label:                                              # property code
                    priority:               20                      # default sort order (will be used in grid and form if not specified)

                    grid:                                           # config for GridBundle (same as in DatagridManager)
                        type:               string
                        label:              'Label'
                        filter_type:        oro_grid_orm_string
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        true
                    form:                                           # Entity update form
                        type:               text                    # field type
                        options:
                            block:          entity                  # field will be rendered in this block ( specified in entity.form.block_config)
                            required:       true                    # is field required or not

        field:                                                      # config block for Entity's Field
            items:                                                  # block of entity

                label:                      ~                       # same as entity.items.label



Below just an example of scope configurations:

    audit:
        entity:
            items:
                auditable:
                    priority:               60
                    grid:
                        type:               boolean
                        label:              'Auditable'
                        filter_type:        oro_grid_orm_boolean
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        false
                    form:
                        type:               choice
                        options:
                            choices:        ['No', 'Yes']
                            empty_value:    false
                            block:          entity
                            label:          'Auditable'
        field:
            items:
                auditable:
                    priority:               60
                    #serializable:           true
                    parent_value:                                           #enabled or not by parent value
                        name:               auditable                       #name of parent field, by default similar with child field name
                        value:              true                            #comparison conditions
                    grid:
                        type:               boolean
                        label:              'Auditable'
                        filter_type:        oro_grid_orm_boolean
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        false
                    form:
                        type:               choice
                        options:
                            choices:        ['No', 'Yes']
                            empty_value:    false
                            block:          entity
                            label:          'Auditable'
    datagrid:
        field:
            form:
                block_config:
                    datagrid:
                        title:              'Datagrid Config'
                        subblocks:
                            base:           ~
            items:
                is_searchable:
                    default_value:          false
                    entity_grid:            false
                    grid:
                        type:               boolean
                        label:              'Datagrid search'
                        filter_type:        oro_grid_orm_boolean
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        false
                    form:
                        type:               choice
                        options:
                            choices:        ['No', 'Yes']
                            empty_value:    false
                            block:          datagrid
                            label:          "Datagrid search"
