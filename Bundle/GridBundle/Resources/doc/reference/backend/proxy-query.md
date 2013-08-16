Proxy Query
-----------

Proxy Query is an objects that encapsulates interaction with DB and provides getter for query object. Proxy Queries are made by Query Factory entities.

#### Class Description

* **Datagrid \ ProxyQueryInterface** - basic interface for Proxy Query;
* **Datagrid \ ORM \ ProxyQuery** - implementation of Proxy Query entity, provides getter for Query Builder;
* **Datagrid \ QueryFactoryInterface**  - interface for Query Factory entity, provide method to create query entity;
* **Datagrid \ ORM \ QueryFactory \ AbstractQueryFactory** - abstract implementation of Query Factory interface, has protected method to create Proxy Query entity;
* **Datagrid \ ORM \ QueryFactory \ QueryFactory** - extended from abstract Query Factory, receives Query Builder as source parameter and creates Proxy Query based on it;
* **Datagrid \ ORM \ QueryFactory \ EntityQueryFactory** - extended from abstract Query Factory, receives Doctrine entity, class name and alias as source parameters and creates Proxy Query based on Query Builder made by Doctrine Entity Repository.

#### Configuration

```
parameters:
    oro_grid.orm.query_factory.entity.class: Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory
    oro_grid.orm.query_factory.query.class:  Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\QueryFactory
```
