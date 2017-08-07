'use strict';

/**
 * Mass edit attribute requirements
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/form'
    ],
    function (_, $, BaseForm) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.triggerModelUpdate);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.triggerModelUpdate);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Update the root model after fake product save
             */
            triggerModelUpdate: function () {
                var data = this.getFormData();
                data.attributes = _.pluck(data.attributes, 'code');
                delete data.meta;

                this.getRoot().trigger('pim_enrich:mass_edit:model_updated', data);

                return this;
            }
        });
    }
);
