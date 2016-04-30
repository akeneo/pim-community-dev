MeasureBundle
=============

Akeneo Measure Bundle : manage measure units in families and conversions from a unit to another

Allows to :
- Convert a value from a unit to another
- Add more unit to a family (group of measure units)
- Create new families

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/MeasureBundle/badges/quality-score.png?s=7ff7ab49c67825534d025a08146ff2b5ea35da1b)](https://scrutinizer-ci.com/g/akeneo/MeasureBundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/akeneo/MeasureBundle/badges/coverage.png?s=3ad7d541f469ab7984879589b3e294aeee52da39)](https://scrutinizer-ci.com/g/akeneo/MeasureBundle/)
[![Build Status](https://travis-ci.org/akeneo/MeasureBundle.png?branch=0.1)](https://travis-ci.org/akeneo/MeasureBundle)

General operation
=================

This bundle to convert a value from a unit to another.
Some families and units are already defined but it's possible to add others families and units to convert anything.

Converter converts a value from a unit to a standard unit (sort of reference unit) then from standard unit to asked unit.
This allows to define just one list of operations to convert each unit.

Operations are defined to convert from the unit to the standard unit.
Converter convert to standard unit with default operations order but reverse order and do the opposite operation (addition <=> substraction and multiplication <=> division).
We used strings to define operations. 'add', 'sub', mul', 'div' are allowed.

You can add more operations extending bundle.


Classes and files
=================

In MeasureBundle :

- Convert/ contains the converter service.
- DependencyInjection/
* Configuration : define how to check configuration measure files
* AkeneoMeasureExtension : define the recovery of the configuration (config files browse and merge configuration).
- Exception/ contains the exception classes used in converter.
- Family/ contains a list of family measure interfaces. Each define a family and must contains a constant named FAMILY.
- Resources/config/
* measure.yml : define measure configuration with a list of families. Each family define a standard unit measure and a list of unit measures. Each measure of this list is defined by a constant then by a conversion rule (list of ordered operations from unit to standard) and a symbol to display.
* services.yml : define measure converter service

```yaml
parameters:
    akeneo_measure.measures_config: ~

services:
    akeneo_measure.measure_converter:
        class: Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter
        arguments: [%akeneo_measure.measures_config%]
```

Configuration file can be seen https://github.com/akeneo/MeasureBundle/blob/master/Resources/config/measure.yml


Install and run unit tests
==========================

To run tests :
```bash
$ php composer.phar update --dev

$ phpunit --coverage-html=cov/
```


Convert a value
===============

A service is defined to use converter. You must call it and define the family to use before convert a value.
In the example below, we convert a value in kilometers to miles.

```php
$converter = $this->container->get('akeneo_measure.measure_converter');
$converter->setFamily(LengthFamilyInterface::FAMILY);
$result = $converter->convert(LengthFamilyInterface::KILOMETER, LengthFamilyInterface::MILE, 1);
```

Get the whole list of families and units
========================================

```php
$this->container->getParameter('akeneo_measure.measures_config');
```

Add unit to an existing family
==============================

To define a new unit in an existing family, it's just necessary to define it and their units in a new config file named measure.yml in your own bundle. For example, in our demo bundle, we add the below code :

```yaml
measures_config:
    Length:
        standard: METER
        units:
            DONG:
                convert: [{'mul': 7},{'div': 300}]
                symbol: dong
```

Here, we just had "Dong" unit with his conversion rules from it to standard unit. To have equivalent to 1 dong in meters, you must multiply by 7 and divide by 300.
A symbol is required too to define unit format to display.
Optionally but recommended, a new class extending family class can be created. It allows to use converter with constants instead of strings. Contants represent config values.
Here we created "MyLengthMeasure" new class extending LengthMeasure to add "Dong" unit constant.

```php
use Akeneo\Bundle\MeasureBundle\Family\LengthFamilyInterface;

/**
 * Override LengthFamily interface to add Dong measure constant
 */
class MyLengthFamilyInterface extends LengthFamilyInterface
{

    /**
     * @staticvar string
     */
    const DONG = 'DONG';

}
```

Then, you can call a conversion to your new unit like this :

```php
$converter = $this->container->get('akeneo_measure.measure_converter');
$converter->setFamily(LengthFamilyInterface::FAMILY);
$result = $converter->convert(LengthFamilyInterface::KILOMETER, MyLengthFamilyInterface::DONG, 1);
```


Create a new family
===================

To create a new family, it's like to add a unit to an existing family. It's necessary to add configuration in measure.yml file of your bundle and optionally a class defining constants to be used instead of strings.

```yaml
measures_config:
    Capacitance:
        standard: FARAD
        units:
            FARAD:
                convert: [{'mul': 1}]
                symbol: F
            KILOFARAD:
                convert: [{'mul': 1000}]
                symbol: kF
            MEGAFARAD:
                convert: [{'mul': 1000000}]
                symbol: MF
```

```php
/**
 * Capacitance measures constants
 */
class CapacitanceFamilyInterface
{

    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Capacitance';

    /**
     * @staticvar string
     */
    const FARAD     = 'FARAD';

    /**
     * @staticvar string
     */
    const KILOFARAD = 'KILOFARAD';

    /**
     * @staticvar string
     */
    const MEGAFARAD = 'MEGAFARAD';

}
```


Exceptions thrown
=================

Exceptions are thrown if we encountered problems during conversion.
- UnknownFamilyMeasureException if you try to use an unexistent or undefined family.
- UnknownMeasureException if you try to convert an unexistent or undefined unit for the family used.
- UnknownOperatorException if you try to use an unexistent operation (authorized add, sub, mul and div)

Divisions by zero don't throw exceptions but are ignored.


Extend converter
================

This bundle is extensible and we can imaginate config recovering from database or services or use converter for currencies for example.
