CHANGELOG 
=========

0.2.0
-----

### Improvements

 - Warnings are stored in their own entities, to avoid excessive serialization

### BC Breaks

 - Warnings are now objects of the Akeneo\BatchBundle\Entity\Warning type
 - Database schema has changed. Please read UPGRADE-0.2.md


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
