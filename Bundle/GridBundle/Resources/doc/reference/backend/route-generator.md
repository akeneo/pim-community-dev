Route Generator
---------------

Route Generator in an entity that generates all service URL's for grid backend and frontend parts based on source route name.

#### Class Description

* **Route \ RouteGeneratorInterface** - basic interface for Route Generator entity;
* **Route \ DefaultRouteGenerator** - implementation of Route generator that receives source data from Parameters entity.

#### Configuration

```
parameters:
    oro_grid.route.default_generator.class: Oro\Bundle\GridBundle\Route\DefaultRouteGenerator
```
