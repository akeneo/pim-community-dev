define(['underscore', 'pim/form', 'oro/mediator', 'oro/datafilter/collection-filters-manager'],
    function (_, BaseForm, mediator, FiltersManager) {

        return BaseForm.extend({
            render() {
                mediator.once('datagrid_filters:loaded', options => {
                    var filtersList = new FiltersManager(options);
                    this.$el.append(filtersList.render().$el);
                    mediator.trigger('datagrid_filters:build.post', filtersList);
                });
            }
        });
    }
);

