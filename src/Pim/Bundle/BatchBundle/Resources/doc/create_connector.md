Create a new connector
----------------------

A minimal connector can be defined as following (note that you can use interface too) :
```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\AbstractConnector;

class MagentoConnector extends AbstractConnector
{
}
```

We declare it as a service with expected configuration FQCN (see configuration section):
```yaml        
parameters:

    connector.magento.class:                   Acme\Bundle\DemoDataFlowBundle\Connector\MagentoConnector
    configuration.magento.class:               Acme\Bundle\DemoDataFlowBundle\Configuration\MagentoConfiguration

services:

    connector.magento:
        class: %connector.magento.class%
        arguments: [%configuration.magento.class%]
```
