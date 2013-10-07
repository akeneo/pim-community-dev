Domain Model
============

Table of Contents
-----------------
 - [Job](#job)
    - [Job Executor](#job-executor)
    - [Job Result](#job-result)
 - [Context](#job)
    - [Context Interface](#context-interface)
    - [Step Execution Proxy Context](#step-execution-proxy-context)
    - [Context Registry](#context-registry)
 - [Reader](#reader)
    - [Reader Interface](#reader-interface)
    - [Csv File Reader](#csv-file-reader)
    - [Entity Reader](#entity-reader)
 - [Processor](#processor)
    - [Processor Interface](#processor-interface)
    - [Import Processor](#import-processor)
    - [Export Processor](#export-processor)
    - [Processor Registry](#processor-registry)
    - [Registry Delegate Processor](#registry-delegate-processor)
 - [Writer](#writer)
    - [Writer Interface](#writer-interface)
    - [Csv File Writer](#csv-file-writer)
    - [Entity Writer](#entity-writer)
    - [Doctrine Clear Writer](#doctrine-clear-writer)
 - [Converter](#converter)
    - [Data Converter Interface](#data-converter-interface)
    - [Default Data Converter](#default-data-converter)
    - [Abstract Table Data Converter](#abstract-table-data-converter)
 - [Strategy](#strategy)
    - [Strategy Interface](#strategy-interface)
    - [Import Strategy Helper](#import-strategy-helper)
 - [Serializer](#serializer)
    - [Serializer](#serializer-1)

Job
---

### Job Executor
**Class:**
Oro\Bundle\ImportExportBundle\Job\JobExecutor

**Description:**
This class should be used to run import/export operations. It encapsulates all interaction with OroBatchBundle
and take care of all details of processing Job in OroBatchBundle. Adds support of transactional execution of jobs
and error handing on exceptions. As a result of execution import/export operation returns instance of Job Result.

**Methods:**
* **executeJob(jobType, jobName, configuration)** - executes a job and returns Job Result

Parameters *jobType* and *jobName* of executeJob method corresponds to configuration of jobs of OroBatchBundle.
Parameter *configuration* is a specific configuration of job, that will be passed to Context. Reader, Processor and
Writer aware of Context and can obtain their configuration from it.

#### Parameters jobType and jobName in jobs configuration o OroBatchBundle

```
connector:
    name: oro_importexport
    jobs:
        entity_export_to_csv: # jobName
            title: "Entity Export to CSV"
            type: export # jobType
            steps:
                export:
                    title:     export
                    reader:    oro_importexport.reader.entity
                    processor: oro_importexport.processor.export_delegate
                    writer:    oro_importexport.writer.csv
```

### Job Result
**Class:**
Oro\Bundle\ImportExportBundle\Job\JobResult

**Description:**
Encapsulates results of import/export execution. When import/export is executed as a result instance of Job Result is
returned. This object contains detailed information about import/export execution status, such as operation
success status, execution context, failure exceptions, job code.

Context
-------

### Context Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Context\ContextInterface

**Description:**
Provide interface for accessing different kind of data that is shared during processing import/export
operation. Such data are:
 * counters (how much records were read/wrote/added/deleted/replaced/updated)
 * errors (error messages and failure exception messages)
 * configuration (set up by controller and used by any class that involved in processing import/export and aware of context)
 * options (custom data that could be accessed by any context aware object)

### Step Execution Proxy Context
**Class:**
Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext

**Description:**
Implementation of Oro\Bundle\ImportExportBundle\Context\ContextInterface. It is a wrapper of
instance of Oro\Bundle\BatchBundle\Entity\StepExecution from OroBatchBundle.

**Oro\Bundle\BatchBundle\Entity\StepExecution**
Instance of this class can store data of step execution, such as number of records were read/write, errors, exceptions,
read warnings and execution context (Oro\Bundle\BatchBundle\Item\ExecutionContext), a storage for abstract data generated during execution.

As import/export domain has it's own terms, ContextInterface expands Oro\Bundle\BatchBundle\Entity\StepExecution
interface and decouples it's clients from knowing about OroBatchBundle.

### Context Registry
**Class:**
Oro\Bundle\ImportExportBundle\Context\ContextRegistry

**Description:**
A storage which gets specific instance of context based on Oro\Bundle\BatchBundle\Entity\StepExecution.
Provides interface to get single instances Contexts using Oro\Bundle\BatchBundle\Entity\StepExecution.

Reader
------

### Reader Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Reader\ReaderInterface

**Description:**
Interface for class that is responsible for reading data from some source. Extends from reader of OroBatchBundle.

### CSV File Reader
**Class:**
Oro\Bundle\ImportExportBundle\Reader\CsvFileReader

**Description:**
Reads data from CSV file. Result of read operation is an array that represents read line from file and keys of this
array are taken from first row or custom header option.

**Configuration Options**

* **filePath** - path to source file
* **delimiter** - CSV delimiter symbol (default ,)
* **enclosure** - CSV enclosure symbol (default ")
* **escape** - CSV escape symbol (default \)
* **firstLineIsHeader** - a flag tells that the first line of CSV file is a header (default true)
* **header** - a custom header

### Entity Reader

**Class:**
Oro\Bundle\ImportExportBundle\Reader\EntityReader

**Description:**
Reads entities using Doctrine. To allow handling large amounts of data without memory lack errors  reading is performed
using Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator which loads data partially using internal batch.

**Configuration Options**

* **entityName** - the name or class name of entity to read
* **queryBuilder** - and instance of custom Doctrine\ORM\QueryBuilder
* **query** - and instance of custom Doctrine\ORM\Query

One option is required, options are mutually exclusive.

Processor
---------

### Processor Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface

**Description:**
Interface for class that is processing import/export operation. Extends from processor of OroBatchBundle.

**Methods:**
* **process(item)** - process import/export operation. Parameter named item comes from reader, it could be an array
read from CSV file or one of the entities queries from Doctrine.

### Import Processor
**Class:**
Oro\Bundle\ImportExportBundle\Processor\ImportProcessor

**Description:**
Basic class for importing. It uses next classes:
* **Data Converter** - to convert array of reader format to array of serializer format.
* **Serializer** - to deserialize output of Data Converter to entity object
* **Strategy** - to perform main logic of import with deserialized entity (Add/Update/Replace/Delete entities)

### Export Processor
**Class:**
Oro\Bundle\ImportExportBundle\Processor\ExportProcessor

**Description:**
Basic class for exporting. It uses next classes:
* **Serializer** - to serialize input entity to array/scalar representation
* **Data Converter** - to convert serialized array to format of writer

### Processor Registry
**Class:**
Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry

Provide a storage of all registered processors that declared by client bundles. Specific processor of some entity
extends basic one (Import Processor or Export Processor) and contains it's own components (Serializer, Data Converter,
Strategy). Such processor should be registered in DIC with tag:

```
services:
    orocrm_contact.importexport.processor.export:
        parent: oro_importexport.processor.export_abstract
        calls:
             - [setDataConverter, [@orocrm_contact.importexport.data_converter.contact]]
        tags:
            - { name: oro_importexport.processor, type: export, entity: %orocrm_contact.entity.class%, alias: orocrm_contact }
```

**Methods:**
* **registerProcessor(ProcessorInterface, type, entityName, alias)** - register processor using input parameters
* **getProcessor(type, alias)** - gets registered processor
* **getProcessorsByEntity(type, entityName)** - gets registered processor by entity, import could have several
processors for some entity, for example one processor for "Add and Replace" import behaviour and other for "Delete"
import behaviour,

### Registry Delegate Processor
**Class:**
Oro\Bundle\ImportExportBundle\Processor\RegistryDelegateProcessor

**Description:**
This processor uses Processor Registry and configuration options from Context to delegate processing.

**Configuration Options**

* **processorAlias** - alias of processor in Processor Registry

Writer
------

### Writer Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Writer\WriterInterface

**Description:**
Interface for class that is responsible for writing data to destination place. Called at the end of each batch
with items, that were first read by Reader and than processed by Processor.

### Csv File Writer
**Interface:**
Oro\Bundle\ImportExportBundle\Writer\CsvFileWriter

**Description:**
This class performs writing of data to CSV file. It is used in export job, when entities are exported to CSV file.

### Csv File Writer
**Class:**
Oro\Bundle\ImportExportBundle\Writer\CsvFileWriter

**Description:**
This class performs writing of data to CSV file. It is used in export job, when entities are exported to CSV file.

### Entity Writer
**Class:**
Oro\Bundle\ImportExportBundle\Writer\EntityWriter

**Description:**
Used in import job. Persists and flushes Doctrine entities, than After clears Doctrine to make possible operations
with large amounts of data without memory limit errors.

**Warning**
Clearing Doctrine can be dangerous and lead to errors with detached entities in Doctrine's Unit of Work. To eliminate
such errors make sure that doctrine listeners doesn't set values to entities from sources other than Doctrine's
repositories.

### Doctrine Clear Writer
**Class:**
Oro\Bundle\ImportExportBundle\Writer\DoctrineClearWriter

**Description:**
Clears Doctrine on each batch. Used in import validation job.


Converter
---------

### Data Converter Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface

**Description:**
Interface for class that is responsible for converting data to export/import format. Used Processor that generally
has it's own Data Converter. Format of input data depends on Serializer results.

**Methods:**
* **convertToExportFormat(exportedRecord, skipNullValues)** - converts exportedRecord to format that will be written
by Writer to destination place
* **convertToImportFormat(importedRecord, skipNullValues)** - converts importedRecord to format that will be used to
deserialize entity from array

### Default Data Converter
**Class:**
Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter

**Description:**
Default converted that is applicable in simple cases of import/export. Can convert data between two
representations: one dimensional vs multi-dimensional arrays. Uses delimiter ":" in keys to convert between these
two formats.

**Example of formats:**
```
// Multi-dimensional
array(
    'name' => array(
        'first_name' => 'John',
        'last_name' => 'Doe',
    )
)
// One-dimensional
array(
    'name:first_name' => 'John',
    'name:last_name' => 'Doe',
)
```

**Methods:**
* **convertToExportFormat(exportedRecord, skipNullValues)** - converts exportedRecord array to one-dimensional array
* **convertToImportFormat(importedRecord, skipNullValues)** - converts importedRecord array to multi-dimensional array

### Abstract Table Data Converter
**Class:**
Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter

**Description:**
Abstract converter that can be extended and used in more complex use cases when you need to provide human readable
names of headers in import/export files. Configured with the rules that will be used to convert data to import/export
formats. See OroCRM\Bundle\ContactBundle\ImportExport\Converter\ContactDataConverter as an example of usage of this
class.

Strategy
--------

### Strategy Interface
**Interface:**
Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface

**Description:**
Interface for class that is responsible for performing import logic operations with entities that were read and
deserialized. The logic could be anything that import should do, for example add all read entities as new, or try to
search entities and if there were found - update them.

**Methods:**
* **process(entity)** - process entity with some logic

### Import Strategy Helper
**Class:**
Oro\Bundle\ImportExportBundle\Strategy\ImportStrategyHelper

**Description:**
A helper class that could be used by specific strategy to perform some generic operations imported records.

**Methods:**
* **importEntity(basicEntity, importedEntity, excludedProperties)** - import values of basicEntity to importedEntity
using Doctrine metadata
* **validateEntity(entity)** - get list of validation errors
* **addValidationErrors(validationErrors, ContextInterface, errorPrefix)** - add validation errors to Context

Serializer
----------

### Serializer
**Class:**
Oro\Bundle\ImportExportBundle\Serializer\Serializer

**Description:**
Class that extends from standard Symfony's serializer and used instead of it to do serialization/deserialization. Has
it's own normalizers/denormalizers that can be added using tags in DI configuration:

```
services:
    oro_user.importexport.user_normalizer:
        class: %oro_user.importexport.user_normalizer.class%
        tags:
            - { name: oro_importexport.normalizer }
```

Each entity that you want to export/import should be supported by import/export Serializer. It means that you should
add normalizers/denormalizers that will take care of converting your entity to array/scalar representation
(normalization during serialization) and vice verse converting array to entity object representation (denormalization
during deserialization).
