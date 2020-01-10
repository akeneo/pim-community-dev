#General behavior

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

##Criteria evaluation
Each criterion has is own php implementation.  
We loop over each attribute values by scope and locale and evaluate the business rule.

The attribute codes on which we have a recommendation are persisted. 

##Axis computation
TODO

##Dashboard consolidation
TODO

##Migration from 3.x to 4.0
The migration scripts handle:
- the tables creation
- the new jobs creation
- the initialization of the dictionaries by activated locales (synchronously)
- the initialization of the evaluation of all the active products of the catalog (asynchronously with the job queue)

##PIM Lifecycle
On product save, the criteria that have to be evaluated are persisted in a `PENDING` status in the `pimee_data_quality_insights_criteria_evaluation` table.  
A Job `data_quality_insights_evaluate_products_criteria` is added to the queue with the product id in parameter.

The job is consumed by the queue.
We find all the pending criteria of the product to evaluate and loop over them.

Each criterion is evaluated one by one.

#Criteria

##Completeness of required attributes
Scope: All required attributes

Calls the completeness service and return all the required attributes with no value
##Completeness of non-required attributes
Scope: All non required attributes

Calls the completeness service and return all the non-required attributes with no value
##Textarea uppercase words
Scope: All textarea attributes

Evaluate the number of words in a textarea that are in uppercase.
##Text title formatting
Scope: For attribute type text, localizable, attribute as main title in the family of the product to evaluate.

Calls Franklin Library to suggest a better formatting.

Example: 
`Macbook air Azerty core I7` should be written `MacBook Air AZERTY Core i7`

##Spelling

###Commands

`pimee:data-quality-insights:schedule-periodic-tasks`

Aim: 
- Generate dictionaries per language code (en, fr) based on product values for spelling criterion
- Consolidate dashboard rates
- Purge old data

Note:
This command line must be in the CRONTAB.


`pimee:data-quality-insights:generate-aspell-dictionary-from-product-values`

Aim:
- Generate the dictionaries per language code each month (based on file timestamp)

Behavior:
Retrieve all the product values per locale. All the words with more than 3 letters, and used more than 10 times in the catalog are considered as part of the dictionary.


`pimee:data-quality-insights:evaluate-pending-criteria -p <product_id>`

Aim:
- Force the evaluation of all the criteria of a product
- For Administration/Support/Dev purpose

`pimee:data-quality-insights:consolidate-dashboard-rates <2020-01-10>`

Aim:
- Force the consolidation of the dashboard rates for a given day
- For Administration/Support/Dev purpose

`pimee:data-quality-insights:demo-helper`

Aim:
- Help the generation of dqi data to display a dashboard
- NEVER RUN IT IN PRODUCTION

###Jobs

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
This Job is added on product save events
