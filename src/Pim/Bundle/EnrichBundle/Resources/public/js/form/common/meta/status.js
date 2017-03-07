'use strict';
/**
 * Updated at extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'oro/mediator',
        'text!pim/template/form/meta/status',
        'pim/common/property'
    ],
    function (_, __, BaseForm, mediator, formTemplate, propertyAccessor) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'AknTitleContainer-metaItem',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                this.label   = __(this.config.label);
                this.value   = __(this.config.value);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                if (this.config.updateOnEvent) {
                    this.listenTo(this.getRoot(), this.config.updateOnEvent, function (newData) {
                        this.setData(newData);
                        this.render();
                    });
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var product = this.getFormData();
                var value = this.config.valuePath ?
                    propertyAccessor.accessProperty(product, this.config.valuePath) : '';

                var html = this.template({
                    label: this.label,
                    value: value
                });

                this.$el.html(html);

                return this;
            }
        });
    }
);
