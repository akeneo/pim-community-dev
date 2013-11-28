Datagrid Views
-------

Views allow to describe pre-defined set of filters and sorters. Each instance of View class can be used with different datagrids, while maintaining same field names acros them.

#### Class Description

* **Datagrid \ Views \ View** - basic class representing a view;
* **Datagrid \ Views \ AbstractViewsList** - Abstract class represents views list, particular view list should extend this class;

#### Configuration

**Configuration of Services**

in datagrid.yml
```
parameters:
    something.datagrid_views_list.class:  Acme\Bundle\SomeBundle\Datagrid\ViewList\SampleViewsList

services:
    # view list
    something.user_datagrid_views_list:
        class:  %something.datagrid_views_list.class%
        arguments:
            - @translator

    oro_user.user_datagrid_manager:
        class: %oro_user.user_datagrid_manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: users
              entity_name: Oro\Bundle\UserBundle\Entity\User
              entity_hint: user
              route_name: oro_user_index
              views_list: something.user_datagrid_views_list
```

**Configuration of Views and View list**

in SampleViewsList one method should be defined
```
    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    protected function getViewsList()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastDay = $now->sub(new \DateInterval('P1D'))->format('m/d/Y H:i:s');

        return array(
            new View(
                'test.user.view.last_active',
                // filters
                array(
                    'enabled' => array(
                        'value' => 1,
                    ),
                    'created' => array(
                        'value' => array(
                            'start' => $lastDay,
                        ),
                        'type' => Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType::TYPE_MORE_THAN
                    ),
                    'username' => array(
                        'value' => 'Jo',
                        'type' => Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType\TextFilterType::TYPE_STARTS_WITH
                    )
                ),
                // sorters
                array(
                    'firstName' => 'DESC',
                )
            ),
            // .. some other view instance
        );
    }
```

Filter and sorter parameters that used here must be the same format as they're coming from request.
Filters type used in example here defined in Oro\Bundle\FilterBundle\Form\Type\Filter\*:
- [DateRangeFilterType](./../../../../../FilterBundle/Form/Type/Filter/DateRangeFilterType.php)
- [TextFilterType](./../../../../../FilterBundle/Form/Type/Filter/TextFilterType.php)
