# How to activate/deactivate jobs depending on a feature flag


This mini documentation explains how to activate/deactivate a job. 

here, for example, `assset export in xslsx` is an existing job that requires the asset manager feature to be activated. It should not be available for the user to run when the assets are not activated.


## Technical explanation

Jobs are registered in a service `JobRegistry`. All jobs are loaded inside this registry with a compiler pass `RegisterJobsPass`.
This registry filters the jobs according to the feature flags.


To register a job available only if the associated feature flag is activated, you have to configure in the DI the `feature` key in `tags`:

```
    akeneo_assetmanager.job.xlsx_asset_export:
        class: '%pim_connector.job.simple_job.class%'
        arguments:
            - 'asset_manager_xlsx_asset_export'
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            -
                - '@akeneo_assetmanager.step.xlsx_asset.export'
            - true
        tags:
            - { name: akeneo_batch.job, connector: '%pim_connector.connector_name.xlsx%', type: '%pim_connector.job.export_type%', feature: 'asset_manager' }   
```

Note: they key `features` is not mandatory. If not provided, the jobs is always activated.


Note: some functions in this `JobRegsitry` service return jobs without paying attention to any feature flag. It's normal as it's mandatory for the installation. Indeed, job instances are persisted in database. If the edition is a Growth Edition, `import product with rules` should be hidden from the user but available in case of an upgrade to Serenity.

## Impacts

- You can't start the job if the associated feature flag is not activated
- The job is not available in the UI
