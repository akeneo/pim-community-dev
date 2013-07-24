Connector registry
------------------

Registry allows to retrieve references to any connector or job services, it equally provides connector to jobs associations list :
```php
<?php
    $registry = $this->container->get('pim_batch.connectors');
    $connectors = $registry->getConnectors();
    $jobs = $registry->getJobs();
    $connectorIdsToJobsIds = $registry->getConnectorToJobs();
```
