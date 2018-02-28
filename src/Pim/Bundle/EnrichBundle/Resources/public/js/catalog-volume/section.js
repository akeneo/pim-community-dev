'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/translator',
        'pim/template/catalog-volume/section'
    ],
    function (
        _,
        BaseForm,
        __,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            initialize: function (options) {
                this.config = Object.assign({}, options.config);

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    title: this.config.title,
                    hint: this.config.hint
                }));
            }
        });
    }
);
