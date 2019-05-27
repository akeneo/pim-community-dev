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
            className: 'AknDefault-bottomPanel AknDefault-bottomPanel--hidden AknMassActions mass-actions',
            collection: null,
            count: 0,
            events: {
                'click .select-all': 'selectAll',
                'click .select-none': 'selectNone',
                'click .select-visible': 'selectVisible',
                'click .select-button': 'toggleButton'
            },

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
                const setCollection = (collection) => {
                    if (null === this.collection) {
                        this.listenTo(collection, 'backgrid:selected', this.select.bind(this));
                    }

                    this.collection = collection;
                    this.updateView();
                };
                this.listenTo(this.getRoot(), 'grid_load:start', setCollection);
                this.listenTo(this.getRoot(), 'grid_load:complete', setCollection);

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    selectedProductsLabel: __(this.config.label),
                    select: __('pim_datagrid.select.title'),
                    selectAll: __('pim_common.all'),
                    selectVisible: __('pim_datagrid.select.all_visible'),
                    selectNone: __('pim_common.none')
                }));

                this.updateView();

                BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Updates the count after clicking in a single event
             *
             * @param {Object}  model The selected model
             * @param {boolean} checked
             */
            select(model, checked) {
                if (checked) {
                    this.count = Math.min(this.count + 1, this.collection.state.totalRecords);
                } else {
                    this.count = Math.max(this.count - 1, 0);
                }

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all" button
             */
            selectAll() {
                this.count = this.collection.state.totalRecords;
                this.collection.trigger('backgrid:selectAll');

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all visible" button
             */
            selectVisible() {
                if (this.count === this.collection.state.totalRecords) {
                    this.count = 0;
                }
                this.collection.trigger('backgrid:selectAllVisible');

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select none" button
             */
            selectNone() {
                this.count = 0;
                this.collection.trigger('backgrid:selectNone');

                this.updateView();
            },

            /**
             * Updates the count (select all or select none), regarding the current count.
             */
            toggleButton() {
                if (this.count === this.collection.state.totalRecords) {
                    this.selectNone();
                } else {
                    this.selectAll();
                }
            },

            /**
             * Updates the current view.
             *
             * In this function, we do not use render() method because:
             * - We need to animate this extension (with CSS)
             * - The events of the sub extensions are lost after re-render.
             */
            updateView() {
                if (this.count > 0) {
                    this.$el.removeClass('AknDefault-bottomPanel--hidden');

                    if (this.count >= this.collection.state.totalRecords) {
                        this.$el.find('.AknSelectButton')
                            .removeClass('AknSelectButton--partial')
                            .addClass('AknSelectButton--selected');
                    } else {
                        this.$el.find('.AknSelectButton')
                            .removeClass('AknSelectButton--selected')
                            .addClass('AknSelectButton--partial');
                    }
                } else {
                    this.$el.addClass('AknDefault-bottomPanel--hidden');

                    this.$el.find('.AknSelectButton')
                        .removeClass('AknSelectButton--selected')
                        .removeClass('AknSelectButton--partial');
                }

                this.$el.find('.count').text(this.count);
            }
        });
    }
);
