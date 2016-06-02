'use strict';
/**
 * Locale structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'text!pim/template/export/product/edit/content/structure/locales',
        'pim/form'
    ],
    function (
        template,
        BaseForm
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change input': 'updateModel'
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.$el.html(
                    this.template({
                        locales: this.getFormData().structure.locales || []
                    })
                );

                this.renderExtensions();
            },
            updateModel: function() {
                var data = this.getFormData();
                data.structure.locales = JSON.parse(event.target.value);
                this.setData(data);
            }
        });
    }
);
