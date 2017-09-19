'use strict';

/**
 * This extension will display the mass actions panel with all the actions available for checked items in a grid.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/grid/mass-actions'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDefault-bottomPanel AknDefault-bottomPanel--hidden',
            collection: null,
            count: 0,

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(this.getRoot(), 'grid_load:start', this.setupCollection.bind(this));

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    selectedProductsLabel: __(this.config.label)
                }));

                BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Attach the collection to the current extension and add listeners
             *
             * @param {Object} collection
             */
            setupCollection(collection) {
                this.collection = collection;

                this.listenTo(this.collection, 'backgrid:selected', this.select.bind(this));
                this.listenTo(this.collection, 'backgrid:selectAll', this.selectAll.bind(this));
                this.listenTo(this.collection, 'backgrid:selectNone', this.selectNone.bind(this));
            },

            /**
             * Updates the count after clicking in a single event
             *
             * @param {Object}  model The selected model
             * @param {boolean} checked
             */
            select(model, checked) {
                if (checked) {
                    this.count++;
                } else {
                    this.count--;
                }

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all" button
             */
            selectAll() {
                this.count = this.collection.state.totalRecords;

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select none" button
             */
            selectNone() {
                this.count = 0;

                this.updateView();
            },

            /**
             * Updates the current view.
             *
             * In this function, we do not use render() method because:
             * - We need to animate this extension (with CSS)
             * - The events of the sub extensions are lost after re-render.
             *
             */
            updateView() {
                if (this.count > 0) {
                    this.$el.removeClass('AknDefault-bottomPanel--hidden');
                } else {
                    this.$el.addClass('AknDefault-bottomPanel--hidden');
                }

                this.$el.find('.count').text(this.count);
            }
        });
    }
);
