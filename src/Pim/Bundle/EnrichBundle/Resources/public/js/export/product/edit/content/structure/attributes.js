'use strict';
/**
 * Attributes structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'text!pim/template/export/product/edit/content/structure/attributes',
        'pim/form'
    ],
    function (
        _,
        __,
        template,
        BaseForm
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change input': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.$el.html(
                    this.template({
                        __: __,
                        attributes: this.getFormData().structure.attributes || []
                    })
                );

                this.delegateEvents();

                this.renderExtensions();
            },

            /**
             * Update the form model on field update
             */
            updateModel: function() {
                var data = this.getFormData();
                data.structure.attributes = JSON.parse(event.target.value);
                this.setData(data);
            }
        });
    }
);
