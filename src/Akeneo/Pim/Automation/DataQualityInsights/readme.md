# General behavior

### Backend
A **criterion** encapsulates the business rules which represent the data quality of a product (a criterion / several criteria).  
Each criterion provide a **grade** (from 0 to 100) depending on the business rule result.  
A criterion is always evaluated by scope and locale.

Two **axes** classifies the criteria: 
 - consistency
 - enrichment

The **axis** is graded by a letter from A to E, this grade is computed thanks to the criteria grades.  

### Frontend
Julia can view **recommendations** for each criterion graded lower than 100 in the **Data Quality Insights TAB** within the PEF.  

Some criteria offers a way to "act" on the recommendation, for example:  
*When we detect spellcheck errors, Julia can approve a suggestion of correction on underlined words.*

The **Dashboard** view is a consolidation of all the axes grades of all the products.  
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
- the initialization of the evaluation of all the active products of the catalog by adding all the criteria of all the products in a `PENDING` status (will be evaluated asynchronously with a job ran by a CRON (NOT the daemon queue))

## PIM Lifecycle
On product save (unitary save only):
- we persist all the criteria in a `PENDING` status in the table `pimee_data_quality_insights_product_criteria_evaluation`
- synchonous evaluations are performed for eligible criteria (providing a fastest feedback loop)
- asynchronous evaluations are performed for criteria that rely on a third party HTTP call

A CRON `pimee:data-quality-insights:evaluations` is configured to run every 30 min.  
To ensure a non-concurrency behavior this CRON adds a job and run it as a subprocess.  
We find all the pending criteria of the product to evaluate and loop over them.
Each CRON instance will evaluate 5000 products maximum.

Each criterion is evaluated one by one.

> Note about the spell check behavior:
> 
> We force the browser to ignore the browser integrated spellchecker and the grammarly plugin to avoir conflicts
> The content of the user is evaluated by our spellchecker class on the fly when the user edit the product to have
> immediate feedback.

# Criteria

## Completeness of required attributes
**Scope:** All required attributes

> Calls the completeness service and return all the required attributes with no value
## Completeness of non-required attributes
**Scope:** All non required attributes

> Calls the completeness service and return all the non-required attributes with no value
## Textarea uppercase words
**Scope:** All textarea attributes

> Evaluate the number of words in a textarea that are in uppercase.
## Textarea lowercase rule
**Scope:** All textarea attributes

> Check if there is capital letters everywhere after punctuation, new line, ...

## Spelling
Scope: For attribute type textarea (without WYSIWYG) and text.

> Calls the **Aspell** linux binary to evaluate the content.  
It uses the generated dictionary based on the catalog of the customer.  
It uses also the words "ignored" by the users thanks to the spellcheck tooltip in the PEF.


# Commands

## Commands usable in production

### Command `pimee:data-quality-insights:evaluations`:

**Aim:**
- Add a `data_quality_insights_evaluations` job instance and run it as a subprocess directly (see job description at the end of this document) 

> **Note:** Recommended to be launched every 30min.

### Command `pimee:data-quality-insights:schedule-periodic-tasks`:

**Aim:**
- Add a `data_quality_insights_periodic_tasks` job instance into the job queue (see job description at the end of this document)

> **Note:** This command line **must be** in the CRONTAB once a day.

### Command `pimee:data-quality-insights:generate-aspell-dictionary-from-product-values`:

**Aim:**
- Generate the dictionaries per language code

> **Note:** Retrieve all the product values per locale. All the words with more than 3 letters, and used more than 10 times in the catalog are considered as part of the dictionary.

### Command `pimee:data-quality-insights:consolidate-dashboard-rates <2020-01-10>`:

**Aim:**
- Force the consolidation of the dashboard rates for a given day
- For Administration/Support/Dev purpose

### Command `pimee:data-quality-insights:purge-outdated-data`:

**Aim:**
- Purge several data use by the Data Quality Insights feature.

> **Note:** The purge is already handled by the periodic tasks but you can use this command to force the purge for a specific day

### Command `pimee:data-quality-insights:health-check --no-ansi`:

**Aim:**
- Provide a health-check tool to understand the customer catalog and the status of criteria evaluation


## Commands not usable in production - use with care

### Command `pimee:data-quality-insights:demo-helper`:

**Aim:**
- Help the generation of DQI data to display a dashboard
- For Administration/Support/Dev purpose ONLY
- **NEVER RUN IT IN PRODUCTION** IT WILL GENERATE FAKE DATA

> **Note:** Evaluate criteria for one product of each family (enough data to have a dashboard).
> 
> You can add the `--full-catalog-evaluation` option to evaluate all the products criteria synchronously.


# Jobs

## List of jobs

### Job `data_quality_insights_periodic_tasks`:

**Aim:**
- Generate dictionaries per language code (en, fr) based on product values for spelling criterion
- Consolidate dashboard rates
- Purge old data

> **Note:** This Job is only added to the queue by the `pimee:data-quality-insights:schedule-periodic-tasks` command

### Job `data_quality_insights_evaluations`:

**Aim:**
- Evaluate the spelling of attribute labels
- Find all the products to evaluate (products updated since the last evaluation)
- Evaluate the `PENDING` criteria

> **Note:** This Job is added by the CRON. This job IS NOT pushed in the job-daemon-queue, it is run as a subprocess of the CRON.
