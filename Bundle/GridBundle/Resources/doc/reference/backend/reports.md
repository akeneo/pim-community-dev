Events
------

Datagrid events are designed to inject external listeners logic into datagrid functionality.
Datagrid events are implemented based on Symfony 2 events using event dispatcher and kernel event listener.

#### Class Description

* **EventDispatcher \ DatagridEventInterface** - basic interface for all datagrid events;
* **EventDispatcher \ AbstractDatagridEvent** - implements datagrid event interface
and extends standard Symfony 2 event;
* **EventDispatcher \ ResultDatagridEven**t (event name: _oro\_grid.datagrid.result_) - allows to
external result listeners to set and get datagrid result rows.

#### Example of usage

**Result Listener**

``` php
namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class EntityResultListener
{
    /**
     * @var string
     */
    protected $datagridName;

    /**
     * @param string $datagridName
     */
    public function __construct($datagridName)
    {
        $this->datagridName = $datagridName;
    }

    /**
     * @param ResultDatagridEvent $event
     */
    public function processResult(ResultDatagridEvent $event)
    {
        if (!$event->isDatagridName($this->datagridName)) {
            return;
        }

        // main processing logic...
    }
}
```

**Configuration**

```
oro_search.datagrid_results.entity_result_listener:
    class: Oro\Bundle\SearchBundle\Datagrid\EntityResultListener
    arguments:
        - oro_search_results
    tags:
        - name: kernel.event_listener
          event: oro_grid.datagrid.result
          method: processResult
```
