Create a new job
----------------

We begin by defining Job class as following (inherits of configure method) :
```php
<?php

namespace Acme\Bundle\DemoDataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Job\AbstractJob;

class ImportAttributesJob extends AbstractJob
{

    public function run()
    {
        // do some stuff ...
    }
}

```

We define job as service and use tags to declare it to the connectors registry and attach it to a conector :
```yaml
parameters:
    job.type.import_customer.class:            Acme\Bundle\DemoDataFlowBundle\Job\ImportCustomersJob

services:
    job.import_attributes:
        class: %job.type.import_attribute.class%
        tags:
            - { name: pim_batch_job, connector: connector.magento_catalog}
```
