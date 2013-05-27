DataFlowBundle
==============

Deal with data import, export, transformation and mapping management

Main classes /  concepts
------------------------

This bundle detects any declared application services which are related to import / export and allows to use them in a generic way.

It makes easy to create your own :
- Connector : a service to group several related jobs (for instance, related to Magento)
- Job : a service which read, transform, write data to process a business operation (for instance, import products from a csv file)
- Configuration : an object to declare and validate required parameters of connector or job

Job uses some basic ETL classes to manipulate data :
- Extractors : to read data from csv file, xml file, excel file, dbal query, orm query, etc
- Tranformers : to convert data (a row / item or a value), as datetime or charset converters, callback converter, etc
- Loaders : to write data to csv, xml, excel file, database table (orm / dbal)

Install
=======

To install for dev :

```bash
$ php composer.phar update --dev
```
To use as dependency, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "oro/DataFlowBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/DataFlowBundle.git",
            "branch": "master"
        }
    ]

```

Run unit tests
==============

```bash
$ phpunit --coverage-html=cov/
```

How to use ?
============

- [Add a connector service](Resources/doc/create_connector.md)
- [Add a job service](Resources/doc/create_job.md)
- [Use registry](Resources/doc/connector_registry.md)
- [Use configurations](Resources/doc/create_configuration.md)
- [Use connector and job instances](Resources/doc/connector_job_instances.md)
- [Make your service configuration editable](Resources/doc/configurable_services.md)

Enhancement
===========
- success / error messages
- refactor controllers to use handlers
- order jobs in a connector + refactor connector controller
- store mapping as configuration
- provide different job classes to deal with different kind of treatment (line by line, massive, etc)
- provide useful classes to make easiest to load high volume of data
