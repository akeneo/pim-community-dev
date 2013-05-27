Parameters
----------

Parameters entity encapsulates all parameters required for grid. Default implementation receives parameters
from Request object.

#### Class Description

* **Datagrid / ParametersInterface** - basic interface for Parameters entity;
* **Datagrid / RequestParameters** - Parameters interface implementation, gets data from Request object.

#### Configuration

```
parameters:
    oro_grid.datagrid.parameters.class: Oro\Bundle\GridBundle\Datagrid\RequestParameters
```
