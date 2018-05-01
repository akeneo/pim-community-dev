'use strict';
/**
 * @author Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/family-variant/labels-container'
    ],
    function(_, __, BaseForm, template) {
        return BaseForm.extend({
            render: function () {
                this.$el.html(
                    _.template(template)({
                        __: __,
                        familyVariant: this.getFormData()
                    })
                );

                this.renderExtensions();
            }
        });
    }
);
