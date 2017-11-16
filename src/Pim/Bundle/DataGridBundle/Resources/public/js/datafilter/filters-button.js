define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'oro/datafilter/collection-filters-manager'
    ], function (
        _,
        BaseForm,
        mediator,
        FiltersManager
    ) {
        return BaseForm.extend({
            /**
             * @inheritdoc
             */
            initialize() {
                mediator.once('datagrid_filters:loaded', this.showFilterManager.bind(this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Creates a new FiltersManager and renders it
             *
             * @param {Object} options
             */
            showFilterManager(options) {
                const filtersList = new FiltersManager(options);
                this.$el.empty().append(filtersList.render().$el);

                mediator.trigger('datagrid_filters:build.post', filtersList);
            }
        });
    }
);

