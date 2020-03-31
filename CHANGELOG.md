## Bug fixes

- Fixes memory leak when indexing product models with a lot of product models in the same family (see https://github.com/akeneo/pim-community-dev/pull/11742)
- PIM-9109: Fix SSO not working behind reverse proxy.
- PIM-9133: Fix product and product model save when the user has no permission on some attribute groups
- PIM-9149: Fix compare/translate on product 
- PIM-9138: Rules import not working with asset manager

## Improvements

- DAPI-834: Data quality - As Julia, when I'm overing the dashboard, I'd like to see the medium grade for a given column.
- DAPI-697: Data quality - As Julia, when I'm on the DQI page, I want to click the attributes that need improvements and land on the PEF.
- DAPI-830: Add more supported languages for data quality text checking
- DAPI-806: Improve criteria evaluations performance
- DAPI-739: Add coefficients by criterion to the calculation of the axes rates
- DAPI-635: Add spellcheck on WYSIWG editors
- DAPI-798: Allow spelling suggestions after a title formatter check
- DAPI-895: As Julia, I'd like spell-check to be available for Norwegian
- DAPI-749: Improve Dashboard rates purge
- RUL-20: Rule engine - As Julia, I would like to copy values from/to different attribute types
- RUL-49: Rule engine - As Peter, I would like to clear attribute values, associations, categories and groups
- RUL-77: Rule engine - As Peter, I would like to add labels to my rules

## New features

- DAPI-854: Data quality - Variant products are also evaluated
- RUL-17: Rules engine - Add the concatenate action type to concatenate some attribute values into a single attribute value

## BC Breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ImpactedProductCountTasklet` to change last argument from `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` to `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
