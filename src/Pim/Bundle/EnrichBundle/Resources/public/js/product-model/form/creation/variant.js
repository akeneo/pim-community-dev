/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/select',
    'pim/form'
],
function (
    $,
    _,
    BaseField,
    BaseForm
) {
    return BaseField.extend({

        configure() {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', form => {
                // Trigger variant display
            })


            return BaseForm.prototype.configure.apply(this, arguments);
        }
    });
});
