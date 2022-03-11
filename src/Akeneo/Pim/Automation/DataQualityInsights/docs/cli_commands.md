# Cli commands for DQI in CE

```shell
bin/console pim:data-quality-insights:consolidate-dashboard-rates                             #Consolidate the Data-Quality-Insights dashboard rates.
bin/console pim:data-quality-insights:evaluate-all-products                                   #Evaluate all products and product models having pending criteria.
bin/console pim:data-quality-insights:evaluations                                             #Launch the evaluations of products and structure
bin/console pim:data-quality-insights:initialize-products-evaluations                         #Initialize the evaluations of all the products and product models.
bin/console pim:data-quality-insights:prepare-evaluations                                     #Prepare the evaluations of products and structure
bin/console pim:data-quality-insights:purge-outdated-data                                     #Purge the outdated data persisted for Data-Quality-Insights.
bin/console pim:data-quality-insights:recompute-product-scores                                #Launch the job that will re-compute all the products scores
bin/console pim:data-quality-insights:schedule-periodic-tasks                                 #Schedule the periodic tasks of Data-Quality-Insights.
bin/console pimee:data-quality-insights:migrate-product-criterion-evaluation                  #Migrate the products criteria evaluations with empty results and pending status.

```


# Cli commands for DQI in EE

```shell
bin/console pim:data-quality-insights:evaluations                                             #Launch the evaluations of products and structure (EE)
bin/console pim:data-quality-insights:populate-product-models-scores                          #Populate scores for existing product models
bin/console pimee:data-quality-insights:demo-helper                                           #DO NOT USE IN PRODUCTION - Command to help generate data quality data for several weeks.
bin/console pimee:data-quality-insights:generate-aspell-dictionary-from-product-values        #Extract most present words in the product values to create a spelling dictionary
bin/console pimee:data-quality-insights:health-check                                          #Display catalog and dqi insights (number of products, attributes, families, channels, locales, ... )

```
