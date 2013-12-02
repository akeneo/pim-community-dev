Overview
========

Table of Contents
-----------------
 - [Main Components](#main-components)
 - [OroBatchBundle Configuration](#orobatchbundle-configuration)
 - [Supported Formats](#supported-formats)
 - [Dependencies](#dependencies)


Main Components
---------------

### Job

OroImportExportBundle uses OroBatchBundle to organize execution of import/export operations.
In the spotlight of OroBatchBundle is a job that can be configured with execution context and executed by client.
Job is abstract by itself, it doesn't know specific details of what is going on during it's execution.

### Step

But every job consists of steps, and each step
aggregates three crucial components:
 * **Reader**
 * **Processor**
 * **Writer**

Each of this three component doesn't know of each other and has it's own responsibility. Step uses the reader
to read data from source, give it to the processor and than take processed result and give it to
the writer.

### Reader

Reads data from some source. In terms of import it can ba a CSV file with imported data. In terms of export the source
is a Doctrine entity, it's repository or more sophisticated query builder.

### Processor

Processor is at the forefront of job execution. Main logic of specific job is concentrated here. Import processor
converts array data to entity object. Export processor does the opposite - converts entity object to array
representation.

### Writer

Writer as the name implies is responsible for saving result in destination. In terms of import it's a storage,
encapsulated with Doctrine. In terms of export it's a plain CSV file.

OroBatchBundle Configuration
----------------------------

This configuration is used by OroBatchBundle and encapsulates three jobs for importing entity to CSV file,
validating import data and exporting entity to CSV file.

```
connector:
    name: oro_importexport
    jobs:
        entity_export_to_csv:
            title: "Entity Export to CSV"
            type: export
            steps:
                export:
                    title:     export
                    reader:    oro_importexport.reader.entity
                    processor: oro_importexport.processor.export_delegate
                    writer:    oro_importexport.writer.csv
        entity_import_validation_from_csv:
            title: "Entity Import Validation from CSV"
            type: import_validation
            steps:
                import_validation:
                    title:     import_validation
                    reader:    oro_importexport.reader.csv
                    processor: oro_importexport.processor.import_validation_delegate
                    writer:    oro_importexport.writer.doctrine_clear

        entity_import_from_csv:
            title: "Entity Import from CSV"
            type: import
            steps:
                import:
                    title:     import
                    reader:    oro_importexport.reader.csv
                    processor: oro_importexport.processor.import_delegate
                    writer:    oro_importexport.writer.entity
```

### Supported Formats

This bundle supports format of CSV file on one side and Doctrine entity on another side.

Dependencies
------------

As was mentioned previously OroBatchBundle is a major dependency of this bundle. OroBatchBundle is used to organize
operations of import/export as batch. But when client bundle is using OroImportExportBundle it doesn't depend directly
from any classes, interfaces or configuration files of OroBatchBundle. OroImportExportBundle provides it's own
interfaces and domain models that client bundle should interact with and from perspective of client bundle it doesn't
need to create any jobs configurations to have support of import/export of some entity.
