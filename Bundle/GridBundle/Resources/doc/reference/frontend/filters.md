Filters
-------

Filters are used to change collection state according to criteria selected by user. Datagrid uses filter functionality provided by OroFilterBundle. It extends filter list and adds additional functionality to work with collection.

Main classes and responsibilities:

* **Oro.Datagrid.Filter.List** - extends Oro.Filter.List from OroFilterBundle and adds methods to work with state and collection
* **Backbone.Collection** - collection of models that has particular state. By setting up filters user updates collection state. After it collection sends request to update it's data accordingly with new state that was applied with filters criteria

Below is example of creating filter list. *oro\_filter\_render\_filter\_javascript* is a twig extension from OroFilterBundle which returns Javascript filter object.
``` javascript
var filtersList = new Oro.Datagrid.Filter.List({
    collection: datagridCollection,
    addButtonHint: '+ Add more',
    filters: {
        username: {{ oro_filter_render_filter_javascript(form.children.username) }},
        gender:   {{ oro_filter_render_filter_javascript(form.children.gender) }},
        salary:   {{ oro_filter_render_filter_javascript(form.children.salary) }}
    }
});
$('#filter').html(filtersList.render().$el);
```
