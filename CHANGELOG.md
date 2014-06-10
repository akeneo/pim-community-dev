CHANGELOG for 0.1.x
===================

0.1.5 (2014-06-10)
------------------

### Features

- StepExecution object is updated on base at every batch writes, allowing to effectively follow the
batch progress


0.1.4 (2014-03-10)
------------------

### Features

- Added AbstractConfigurableStepElement::initialize() and AbstractConfigurableStepElement::flush()

### BC Breaks

- Renamed ItemStep::initializeStepComponents() to ItemStep::initializeStepElements()
