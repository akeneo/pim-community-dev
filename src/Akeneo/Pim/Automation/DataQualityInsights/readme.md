# General behavior

A product is evaluated regarding some criteria.  
Each criterion provide a grade (from 0 to 100).  
If a criterion is lower than 100, some piece of advice, called recommendations are displayed to the user.  
Those recommendations are displayed in the PEF, in the Data Quality Insights tab.  
The evaluations of the criteria are asynchronously performed by a job.

The criteria are grouped by axis (consistency, enrichment, ...)  
The axis is graded by a letter from A to E, this grade is computed with the criteria grades.  

Some criteria offer a way to "act" on the recommendation, like "Title formatting" or "Spell Check" for example.  
For the "Spell Check" criterion, on the PEF, when we detect errors, the errors are underlined and a suggestion of correction is shown.

A criterion is always evaluated by scope and locale.

The Dasboard view is a consolidation of all the evaluations of all the products.  
We can filter the dashboard by scope/locale/(family or category) by day/week/month.

## Criteria evaluation
Each criterion has is own php implementation.  
We loop over each attribute values by scope and locale and evaluate the business rule.

The attribute codes on which we have a recommendation are persisted. 

## Axis computation
TODO

## Dashboard consolidation
TODO

## Migration from 3.x to 4.0
The migration scripts handle:
- the tables creation
- the new jobs creation
- the initialization of the dictionaries by activated locales (synchronously)
- the initialization of the evaluation of all the active products of the catalog by adding all the criteria of all the products in a `PENDING` status (will be evaluated asynchronously with a job ran by a CRON (NOT THE daemon queue))

## PIM Lifecycle
On product save, the criteria that have to be evaluated are persisted in a `PENDING` status in the `pimee_data_quality_insights_criteria_evaluation` table.  

A CRON `pimee:data-quality-insights:evaluate-products` is configured to run every 30 min.
This CRON add a job and run it as a subprocess (to handle a logical non-concurrency behavior)
We find all the pending criteria of the product to evaluate and loop over them.
Each CRON instance will evaluate 2500 products maximum.

Each criterion is evaluated one by one.

> Special note about the spell check behavior:
> 
> We force the browser to ignore the browser integrated spellchecker and the grammarly plugin to avoir conflicts
> The content of the user is evaluated by our spellchecker class on the fly when the user edit the product to have
> immediate feedback.

# Criteria

## Completeness of required attributes
Scope: All required attributes

Calls the completeness service and return all the required attributes with no value
## Completeness of non-required attributes
Scope: All non required attributes

Calls the completeness service and return all the non-required attributes with no value
## Textarea uppercase words
Scope: All textarea attributes

Evaluate the number of words in a textarea that are in uppercase.
## Textarea lowercase rule
Scope: All textarea attributes

Check if there is capital letters everywhere after punctuation, new line,...
## Text title formatting
Scope: For attribute type text, localizable, attribute as main title in the family of the product to evaluate.

Calls Franklin Library to suggest a better formatting.

Example: 
`Macbook air Azerty core I7` should be written `MacBook Air AZERTY Core i7`

## Spelling
Scope: For attribute type textarea (without WYSIWIG) and text.

Calls the Aspell linux binary to evaluate the content.  
It uses the generated dictionary based on the catalog of the customer.  
It uses also the words "ignored" by the users.  




### Commands

#### Commands usable in production

`pimee:data-quality-insights:schedule-periodic-tasks`

Aim: 
- Generate dictionaries per language code (en, fr) based on product values for spelling criterion
- Consolidate dashboard rates
- Purge old data

Note:
This command line must be in the CRONTAB once a day.

`pimee:data-quality-insights:generate-aspell-dictionary-from-product-values`

Aim:
- Generate the dictionaries per language code

Behavior:
Retrieve all the product values per locale. All the words with more than 3 letters, and used more than 10 times in the catalog are considered as part of the dictionary.

`pimee:data-quality-insights:evaluate-products`

Aim:
- Launch the evaluation of all the pending criteria of all the products

Note:
Aim to be launched every 30min.

`pimee:data-quality-insights:consolidate-dashboard-rates <2020-01-10>`

Aim:
- Force the consolidation of the dashboard rates for a given day
- For Administration/Support/Dev purpose

`pimee:data-quality-insights:purge-outdated-data`

Aim:
- Purge several data use by the Data Quality Insights feature.

Note:
The purge is already handled by the periodic tasks but you can use this command to force the purge for a specific day

`bin/console pimee:data-quality-insights:health-check --no-ansi`

Aim:
- Provide a health-check tool to understand the customer catalog and the status of criteria evaluation


#### Commands not usable in production - use with care

`pimee:data-quality-insights:demo-helper`

Aim:
- Help the generation of dqi data to display a dashboard
- For Administration/Support/Dev purpose
- NEVER RUN IT IN PRODUCTION IT WILL GENERATE FAKE DATA

Note:
Evaluate criteria for one product of each family (enough data to have a dashboard)  
You can add the `--full-catalog-evaluation` option to evaluate all the products criteria synchronously


### Jobs

#### List of jobs

`data_quality_insights_periodic_tasks`

Aim:
- Generate dictionaries per language code (en, fr) based on product values for spelling criterion
- Consolidate dashboard rates
- Purge old data

Note:
This Job is only added to the queue by the `pimee:data-quality-insights:schedule-periodic-tasks` command

`data_quality_insights_evaluate_products_criteria`

Aim:
- Evaluate the criteria of product id(s) in parameter of the job.

Note:
This Job is added by the CRON. This job IS NOT pushed in the job-daemon-queue, it is run as a subprocess of the CRON.
