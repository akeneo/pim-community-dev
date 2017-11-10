'use strict';

define([
        'underscore',
        'pim/form',
        'pim/common/grid',
        'oro/translator'
    ],
    function (
        _,
        BaseForm,
        Grid,
        __
    ) {
        return BaseForm.extend({
            className: 'tabbable tabs-left history',
            historyGrid: null,

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                BaseForm.prototype.initialize.call(this, arguments);

                this.config.modelDependent = false;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.config.tabCode ? this.config.tabCode : this.code,
                    label: __(this.config.title)
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
                            object_class: this.config.class,
                            object_id: this.getFormData().meta.id
                        }
                    );
                }

                this.$el.empty().append(this.historyGrid.render().$el);

                return this;
            }
        });
    }
);
