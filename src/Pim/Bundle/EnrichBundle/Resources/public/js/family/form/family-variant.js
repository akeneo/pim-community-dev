'use strict';

define([
        'underscore',
        'pim/form',
        'pim/common/grid',
        'oro/translator',
        'pim/template/family/tab/family-variant'
    ],
    function (
        _,
        BaseForm,
        Grid,
        __,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable variant',
            variantGrid: null,

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);
                this.config.modelDependent = false;

                return BaseForm.prototype.initialize.apply(this, arguments);
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
                if (!this.variantGrid) {
                    this.variantGrid = new Grid(
                        'family-variant-grid',
                        {
                            family_id: this.getFormData().meta.id,
                            localeCode: 'en_US'
                        }
                    );
                }

                this.$el.html(this.template());

                this.renderExtensions();
                this.getZone('grid').appendChild(this.variantGrid.render().el);
            }
        });
    }
);
