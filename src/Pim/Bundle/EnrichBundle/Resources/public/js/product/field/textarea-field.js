'use strict';
/**
 * Textarea field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/field', 'underscore', 'text!pim/template/product/field/textarea', 'summernote'],
    function (Field, _, fieldTemplate) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'textarea',
            events: {
                'change textarea': 'updateModel'
            },
            render: function () {
                Field.prototype.render.apply(this, arguments);

                if (this.attribute.wysiwyg_enabled) {
                    this.$('textarea').summernote({
                        disableResizeEditor: true,
                        height: 200,
                        iconPrefix: 'icon-',
                        toolbar: [
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol']],
                            ['insert', ['link']],
                            ['view', ['codeview']]
                        ]
                    }).on('summernote.blur', _.bind(this.updateModel, this));
                }

            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function () {
                this.setCurrentValue(this.$('textarea').code());
            },
            setFocus: function () {
                this.$('textarea').first().focus();
            }
        });
    }
);
