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
    - [Registry Delegate Processor](#registry-delegate-processor)
 - [Converter](#converter)
 - [Writer](#writer)

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
Reads data from CSV file. Configuration supports next options:

* **filePath** - path to source file
* **delimiter** - CSV delimiter symbol (default ,)
* **enclosure** - CSV enclosure symbol (default ")
* **escape** - CSV escape symbol (default \)
* **firstLineIsHeader** - a flag tells that the first line of CSV file is a header (default true)
* **header** - a custom header

Result of read operation is an array that represents read line from file and keys of this array are taken from first
row or custom header option.

### Entity Reader

**Class:**
Oro\Bundle\ImportExportBundle\Reader\EntityReader

**Description:**
Reads entities using Doctrine. To allow handling large amounts of data without memory lack errors  reading is performed
using Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator which loads data partially using internal batch.

Configuration supports next options:

* **entityName** - the name or class name of entity to read
* **queryBuilder** - and instance of custom Doctrine\ORM\QueryBuilder
* **query** - and instance of custom Doctrine\ORM\Query

Class requires at one option, options are mutually exclusive.

Processor
---------

### Processor Interface

### Import Processor

### Export Processor

### Registry Delegate Processor


Writer
------


