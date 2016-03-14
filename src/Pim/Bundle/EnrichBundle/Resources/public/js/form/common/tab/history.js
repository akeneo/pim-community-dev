'use strict';

define([
        'pim/form',
        'pim/common/grid'
    ],
    function(
        BaseForm,
        Grid
    ) {
        return BaseForm.extend({
            className: 'tabbable tabs-left history',
            historyGrid: null,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.variant_group.tab.history.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.historyGrid) {
                    this.historyGrid = new Grid(
                        'history-grid',
                        {
                            object_class: 'Pim\\Bundle\\CatalogBundle\\Entity\\Group',
                            object_id: this.getFormData().meta.id
                        }
                    );
                }

                this.$el.empty().append(this.historyGrid.render().$el);

                return this;
            },
        });
    }
);
