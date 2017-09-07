define(['underscore', 'pim/form', 'oro/mediator'],
    function (_, BaseForm, mediator) {

        return BaseForm.extend({
            filters: [],
            className: 'filters-list',

            initialize() {
                mediator.once('datagrid_filters:loaded', this.filtersUpdated.bind(this));
                BaseForm.prototype.initialize.apply(this, arguments);
            },

            filtersUpdated(filters) {
                this.filters = filters;
                this.render();
            },

            render() {
                _.each(this.filters, function (filter) {
                    if (!filter.enabled) {
                        filter.hide();
                    }
                    if (filter.enabled) {
                        filter.render();
                    }
                    if (filter.$el.length > 0) {
                        this.$el.append(filter.$el.get(0));
                    }
                }, this);
            }
        });
    }
);

