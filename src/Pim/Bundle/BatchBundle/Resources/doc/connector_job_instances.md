Connector and job intances
==========================

These classes allow to store in a doctrine backend a configured instance of a connector or job service.

```php
    // create job
    $job = new Job();
    $job->setServiceId('my.job.service..id');
    $job->setDescription('my job description');
    $configuration = new Configuration();
    $job->setConfiguration($configuration);

    // create connector
    $connector = new Connector();
    $connector->setServiceId('my.connector.id');
    $connector->setDescription('my description');
    $configuration = new Configuration();
    $connector->setConfiguration($configuration);
    $connector->addJob($job);

    $manager->persist($connector);
```

