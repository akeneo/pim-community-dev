/**
 * Generic field to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/form/creation/field'
], function($, _, __, BaseForm, template) {

    return BaseForm.extend({
        template: _.template(template),
        dialog: null,
        events: {
            'change input': 'updateModel'
        },

        /**
     * {@inheritdoc}
     */
        initialize: function(config) {
            this.config = config.config;
            this.identifier = this.config.identifier || 'code';

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
     * Model update callback
     */
        updateModel: function(event) {
            this.getFormModel().set(this.identifier, event.target.value || '');
        },

        /**
     * {@inheritdoc}
     */
        render: function() {
            if (!this.configured)
                this;

            const errors = this.getRoot().validationErrors || [];

            this.$el.html(this.template({
                identifier: this.identifier,
                label: __(this.config.label),
                requiredLabel: __('pim_common.required_label'),
                errors: errors.filter(error => {
                    const id = this.identifier;
                    const {path, attribute} = error;

                    return id === path || id === attribute;
                }),
                value: this.getFormData()[this.identifier]
            }));

            this.delegateEvents();

            return this;
        }
    });
});
