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
    template,
) {
    return BaseForm.extend({
        className: 'AknGridToolbar-right AknDisplaySelector AknDropdown AknButtonList-item',
        config: {},
        template: _.template(template),
        gridName: null,
        events: {
            'click li': 'setDisplayType'
        },

        initialize(options) {
            this.gridName = options.config.gridName;

            return BaseForm.prototype.initialize.apply(this, arguments);
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

        getStoredType() {
            return localStorage.getItem(`display-selector:${this.gridName}`);
        },

        setDisplayType(event) {
            let type = this.$(event.target).data('type');

            localStorage.setItem(`display-selector:${this.gridName}`, type);

            return this.getRoot().trigger('grid:display-selector:change', type);
        },

        renderDisplayTypes(types) {
            const firstType = Object.keys(types)[0];
            const selectedType = this.getStoredType() || firstType;

            this.$el.html(this.template({ types, selectedType }));

            return BaseForm.prototype.render.apply(this, arguments);
        }
    });
});
