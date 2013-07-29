Reports
-------

Datagrid bundle provides basic functionality to build reports based on defined structure. PHP array or YAML string
supported as a storage.

#### Structure definition

- *name* - report name, reserved for future use
- *distinct* - boolean, indicates that query should use DISTINCT keyword (false by default)
- *select* - string representing column list to select
- *from* - array of table/alias declarations. Each element is an array with the following keys:
    *table* - entity declaration, for example "OroUserBundle:User"
    *alias* - [optional] alias for the entity/table name
- *join* - array of join declarations. May contain two children:
    *inner* - adds inner join. Format:
        *join* - entity relation name, for example "u.articles"
        *alias* - [optional] alias for a relation
    *left* - adds left join. Format:
        *join* - entity relation name, for example "u.articles"
        *alias* - [optional] alias for a relation
- *where* - array of where declarations. May contain two children:
    *and* - adds "AND" where clause. You can use multiple declarations as array elements.
    *or* - adds "OR" where clause. You can use multiple declarations as array elements.
- *groupBy* - group definition as a string
- *having* - "HAVING" clause definition as a string
- *orderBy* - array of order declarations. Format:
    *column* - order column
    *dir* - [optional] sort direction, "asc" by default


#### Example of usage

**YAML definition**

``` yaml
reports:
    -
        name:     DemoReport
        distinct: false
        select:   "u.username, u.firstName, u.loginCount, a.apiKey, COUNT(u.firstName) AS cnt"
        from:
            - { table: OroUserBundle:User, alias: u }
        join:
            left:
                - { join: u.api, alias: a }
                - { join: u.statuses, alias: s }
        where:
            and:
                - "u.loginCount >= 0"
            or:
                - "u.enabled = 0"
        groupBy:  "u.firstName"
        having:   "COUNT(u.firstName) > 1"
        orderBy:
            - { column: "u.firstName", dir: "asc" }
```

``` php

```

**Configuration**

services.yml
``` yaml
parameters:
    acme_demo_grid.report_grid.manager.class: Acme\Bundle\DemoGridBundle\Datagrid\ReportDatagridManager

services:
acme_demo_grid.report_grid.manager:
    class: %acme_demo_grid.report_grid.manager.class%
    tags:
        - name: oro_grid.datagrid.manager
          datagrid_name: report
          entity_hint: reports
          route_name: acme_demo_gridbundle_report_list
```

**Datagrid declaration**

``` php
<?php

namespace Acme\Bundle\DemoGridBundle\Datagrid;

use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\QueryConverter\YamlConverter;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ReportDatagridManager extends DatagridManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        // ...
    }

    /**
     * {@inheritdoc}
     */
    protected function createQuery()
    {
        $input     = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/reports.yml'));
        $converter = new YamlConverter();

        $this->queryFactory->setQueryBuilder(
            $converter->parse($input['reports'][0], $this->entityManager)
        );

        return $this->queryFactory->createQuery();
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
```

**Controller**

``` php
<?php

namespace Acme\Bundle\DemoGridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/list.{_format}",
     *      name="acme_demo_gridbundle_report_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     */
    public function listAction()
    {
        $gridManager = $this->get('acme_demo_grid.report_grid.manager');

        $gridManager->setEntityManager($this->getDoctrine()->getManager());

        $grid     = $gridManager->getDatagrid();
        $gridView = $grid->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($gridView);
        }

        return array('datagrid' => $gridView);
    }
}
```