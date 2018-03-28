# 0.6.0 (2017-01-21)

## Functional improvements
 - The measure manager is now able to return the list of unit codes for a given family
 - The measure manager is now able to tell if a unit code exists for a given family

## BC breaks
 - remove `unitExistsInFamily` from `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
 - Add `unitSymbolExistsInFamily` `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
 - Add `unitCodeExistsInFamily` in `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
 - Add `getUnitCodesForFamily` in `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`

# 0.5.0 (2016-04-30)
 - Use phpspec and not phpunit anymore, thanks to @fitn
 - Fix missing family constants, thanks to @danielsan80
 - Add a new pressure family, thanks to @gplanchat

# 0.4.1 (2016-03-16) 
 - Update symbol for Decibel

# 0.4.0 (2015-08-30)
 - fix deprecated use of Yaml:parse() to allow Symfony upgrade

# 0.2.4 (2014-11-07)
 - Add a method unitExistsInFamily in MeasureManager

# 0.2.3 (2014-08-29)
 - Added MILLIAMPEREHOUR

# 0.2.2 (2014-08-18)
 -  Corrected missing definition for CENTICOULOMB

# 0.2.1 (2014-08-13)
 - Added Intensity, Electric charge, Duration and Voltage families

# 0.2.0 (2014-05-27)
 - Added Frequency and Decibel families

# 0.1.0 (2014-02-07)

## Features
 - Initial release

## Improvements
 - Remove need for composer.lock
 - Uses minimum-stability stable

## Bug fixes
- Fix typo on CELCIUS

