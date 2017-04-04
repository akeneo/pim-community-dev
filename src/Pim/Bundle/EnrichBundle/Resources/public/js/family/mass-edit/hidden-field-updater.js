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
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var data = this.getFormData();
                data.attributes = _.pluck(data.attributes, 'code');
                delete data.meta;

                var stringifiedData = JSON.stringify(data, null, 0);
                $('#pim_enrich_mass_edit_choose_action_operation_values').val(stringifiedData);

                return this;
            }
        });
    }
);
