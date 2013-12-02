Config(yml) Example
====================

``` yaml
oro_entity_config:
    # An example of 'entity' scope configuration
    entity:                                                         # configuration scope name
        entity:                                                     # config block for Entity instance

            form:                                                   # A configuration of a form used to configure an entity
                block_config:                                       #
                    entity:                                         # A name of form block
                        priority:           20                      # A display order (sort order) of this form block. This is an optional attribute
                        title:              'General'               # A title of this form block
                        subblocks:                                  # Form sub blocks configuration
                            base:
                                title:      'General Information'

            items:                                                  # A configuration of Entity properties

                label:                                              # A property code
                    options:                                        # A property options
                        priority:           20                      # The default sort order (will be used in grid and form if not specified)

                    grid:                                           # Define how this property is displayed in a data grid (same as in DatagridManager)
                        type:               string
                        label:              'Label'
                        filter_type:        string
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        true
                    form:                                           # Define how this property is displayed on the Entity update form
                        type:               text                    # A form field type
                        options:
                            block:          entity                  # A name of form block this field will be rendered ( specified in entity.form.block_config)
                            subblock:       base                    # A name of form sub block this field will be rendered ( specified in entity.form.block_config.subblocks)
                            required:       true                    # Specify whether this field is required or not

        field:                                                      # A configuration of a form used to configure entity field
            items:
                auditable:
                    options:
                        priority:           60
                        serializable:       true
                    grid:
                        type:               boolean
                        label:              'Auditable'
                        filter_type:        boolean
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
```
