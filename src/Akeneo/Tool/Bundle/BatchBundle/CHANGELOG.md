CHANGELOG
=========

0.5.x (2017-xx-xx)
------------------

- Upgrade minimal version to PHP 7.1

### Bug fixes

- PIM-5120: Do not hydrate all JobExecution of a JobInstance during a the creation of a JobExecution

0.4.5 (2015-11-04)
------------------

### Bug fixes

- PIM-5120: Do not hydrate all JobExecution of a JobInstance during a the creation of a JobExecution

0.4.4 (2015-09-03)
------------------

### Bug fixes

- Fix composer requirements to minor versions

0.4.3 (2015-08-30)
------------------

### Bug fixes

- PIM-4678: Remove execution detail in mail notifications
- Remove execution detail in mail notifications

### Improvements

- Move the JobInstanceFactory from Akeneo PIM
- Fix inconsistencies with ItemStep and item elements interfaces and methods flush() and initialize()
- Add a command to create job instances

0.4.2
-----

### Bug fixes

- Fix missing akeneo_batch.entity.job_execution.class parameter
- Fix erroneous class name in DoctrineJobRepository

0.4.1
-----

### Features

- Added the `SimpleJobLauncher` to ease job launches

### Improvements

- Introduced PHPspec as a dev dependency

0.4.0
-----

### Improvements

- The `--no-log` option is now used instead of the console related `--no-debug` option

0.3.8 (2015-11-04)
------------------

### Bug fixes

- PIM-5120: Do not hydrate all JobExecution of a JobInstance during a the creation of a JobExecution

0.3.7 (2015-09-03)
------------------

### Bug fixes

- Fix composer requirements to minor versions

0.3.6 (2015-08-03)
------------------

### Bug fixes

- PIM-4678: Remove execution detail in mail notifications

0.3.5
-----

### Features

- Added optional arguments $code and $previous in the constructor of InvalidItemException

0.3.4
-----

### Bug fixes

- Added a UOW clear in order to prevent job instance to be flushed

0.3.3
-----

### Features

- Add an optional parameter in StepExecution::incrementSummaryInfo


0.3.2
-----

### Bug fixes

- SQL errors on PostgreSQL due to reserved keyword

0.3.1
-----

### Bug fixes

- Fix the memory overflow with warnings

0.3.0
-----

### Features

- Added a user property in JobExecution to allow to store the user who launched the job

### BC Breaks

 - Database schema has changed. Please read UPGRADE.md


0.2.8 (2015-08-03)
------------------

### Bug fixes

- PIM-4678: Remove execution detail in mail notifications

0.2.7 (2015-01-07)
------------------

### Bug fixes

- PIM-3562: Added a UOW clear in order to prevent the job instance to be flushed

0.2.6
-----

### Bug fixes

- PIM-3446: Fix memory issue when loading a lot of warning

0.2.5
-----

### Bug fixes

- Fix/verify pid

0.2.4
-----

### Bug fixes

- Add a jobExecutionManager allowing to check if a job is still running

0.2.3
-----

### Bug fixes

- Fix the deprecated call to getEntityManager to avoid warnings during batch runs.

0.2.2
-----

### Bug fixes

- Add a new case to deal with an object item in addWarning


0.2.1
-----

### Bug fixes

- Adding the name of the job to the message at the end of the execution.

0.2.0
-----

### Improvements

 - Warnings are stored in their own entities, to avoid excessive serialization

### BC Breaks

 - Warnings are now objects of the Akeneo\BatchBundle\Entity\Warning type
 - Database schema has changed. Please read UPGRADE.md


0.1.8 (2014-06-24)
------------------

### Improvements

 - Added precise error messages for failures on jobs.


0.1.7 (2014-06-24)
------------------

### Features

- Added akeneo:batch:list-jobs command


0.1.6 (2014-06-10)
------------------

### Features

- StepExecution object is updated to repository at every batch writes, allowing to effectively follow the
batch progress
- JobExecution has now the PID of the system process executing the job

### BC Breaks
- a doctrine:schema:update call is required to update database schema to add the pid column to JobExecution


0.1.5 (2014-05-28)
------------------

### Features

- Configure a step element configuration value via its setter


0.1.4 (2014-03-10)
------------------

### Features

- Added AbstractConfigurableStepElement::initialize() and AbstractConfigurableStepElement::flush()

### BC Breaks

- Renamed ItemStep::initializeStepComponents() to ItemStep::initializeStepElements()
