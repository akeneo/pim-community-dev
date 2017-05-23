/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'text!pim/template/attribute/tab/properties/boolean'
],
function (
    _,
    __,
    BaseForm,
    template
) {
    return BaseForm.extend({
        className: 'AknFieldContainer',
        template: _.template(template),
        config: {},
        events: {
            'change input': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (meta) {
            this.config = meta.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        render: function () {
            if (undefined === this.config.fieldName) {
                throw new Error('The view "' + this.code + '" must be configured with a field name.');
            }

            var fieldName = this.config.fieldName;

            this.$el.html(this.template({
                value: this.getFormData()[fieldName],
                fieldName: fieldName,
                labels: {
                    field: __('pim_enrich.form.attribute.tab.properties.' + fieldName),
                    on: __('switch_on'),
                    off: __('switch_off')
                }
            }));

            this.$('.switch').bootstrapSwitch();

            this.renderExtensions();
            this.delegateEvents();
        },

        /**
         * @param {Object} field
         */
        updateModel: function (field) {
            var newData = {};
            newData[this.config.fieldName] = $(field).is(':checked');

            this.setData(newData);
        }
    });
});
