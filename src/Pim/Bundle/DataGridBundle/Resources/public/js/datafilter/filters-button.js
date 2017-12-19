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
            displayAsPanel: false,
            className: 'Toto',

            /**
             * @inheritdoc
             */
            initialize(meta) {
                this.displayAsPanel = undefined === meta.config.displayAsPanel ? false : meta.config.displayAsPanel;

                mediator.once('datagrid_filters:loaded', this.showFilterManager.bind(this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Creates a new FiltersManager and renders it
             *
             * @param {Object} options
             */
            showFilterManager(options) {
                options.displayAsPanel = this.displayAsPanel;

                const filtersList = new FiltersManager(options);

                this.$el.append(filtersList.render().$el);

                mediator.trigger('datagrid_filters:build.post', filtersList);
            }
        });
    }
);

