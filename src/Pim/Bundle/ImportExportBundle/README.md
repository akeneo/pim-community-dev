Import and Export Bundle
========================

This bundle provides an export command among with some configuration that allows to export data into various formats

Basic configuration
-------------------

``` yaml
pim_import_export:
    encoders:
        csv:
            delimiter: ';'
            enclosure: '"'
            with_header: true
    exporters:
        product:
            format: csv
            reader:
                type: Pim\Bundle\ImportExportBundle\Reader\DoctrineReader
                options:
                    em: @doctrine.orm.default_entity_manager
                    entity: "PimCatalogBundle:Product"
                    method_name: findBy
                    method_params:
                        - { enabled: false }
            writer:
                type: Pim\Bundle\ImportExportBundle\Writer\FilePutContentsWriter
                options:
                    path: "/tmp/export.csv"
```
