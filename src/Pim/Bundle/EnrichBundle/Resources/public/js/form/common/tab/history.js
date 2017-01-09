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
            className: 'AknTabContainer-content tabbable tabs-left history',
            historyGrid: null,

            /**
             * @param {Object} config
             */
            initialize: function (config) {
                this.config = _.extend({}, config.config);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', function () {
                    var model = this.getParent().getParent().getFormData();
                    if (0 < model.code.length) {
                        this.trigger('tab:register', {
                            code: this.code,
                            label: __(this.config.title)
                        });
                    }
                }.bind(this));

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
