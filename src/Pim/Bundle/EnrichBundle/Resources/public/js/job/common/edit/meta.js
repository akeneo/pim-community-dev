'use strict';
/**
 * Label extension for jobs
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form', 'underscore', 'oro/translator', 'pim/template/export/common/edit/meta'],
    function (BaseForm, _, __, template) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    jobInstance: this.getFormData(),
                    __: __
                }));

                return this;
            }
        });
    }
);
