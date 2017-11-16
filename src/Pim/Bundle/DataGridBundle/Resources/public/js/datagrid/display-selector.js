define([
    'jquery',
    'underscore',
    'backbone',
    'oro/translator',
    'pim/form',
    'pim/template/datagrid/display-selector'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm,
    template
) {
    return BaseForm.extend({
        className: 'AknGridToolbar-right AknDisplaySelector',
        config: {},
        template: _.template(template),
        events: {
            'click li': 'setDisplayType'
        },

        /**
         * @inheritDoc
         */
        configure() {
            this.listenTo(this.getRoot(), 'grid_load:start', this.collectDisplayOptions.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        collectDisplayOptions(collection, gridView) {
            const displayTypes = gridView.options.displayTypes;

            if (undefined === displayTypes) {
                return;
            }

            this.renderDisplayTypes(displayTypes);
        },

        setDisplayType(event) {
            const type = this.$(event.target).data('type');

            if ('default' === type)  {
                return this.getRoot().trigger('grid:display-selector:reset');
            }

            return this.getRoot().trigger('grid:display-selector:change', type);
        },

        renderDisplayTypes(types) {
            this.$el.html(this.template({ types }));

            return BaseForm.prototype.render.apply(this, arguments);
        }
    });
});
