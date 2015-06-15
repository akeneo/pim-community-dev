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
    [
        'pim/field',
        'underscore',
        'pim/attribute-manager',
        'text!pim/template/product/field/textarea',
        'summernote'
    ],
    function (
        Field,
        _,
        AttributeManager,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'textarea',
            events: {
                'change textarea': 'updateModel',
                'blur textarea': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            postRender: function () {
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
            updateModel: function () {
                if (this.attribute.wysiwyg_enabled) {
                    var data = this.$('textarea').code();
                } else {
                    var data = this.$('textarea:first').text();
                }
                data = '' === data ? AttributeManager.getEmptyValue(this.attribute) : data;

                this.setCurrentValue(data);
            },
            setFocus: function () {
                if (this.attribute.wysiwyg_enabled) {
                    this.$('textarea:first').summernote('focus');
                } else {
                    this.$('textarea:first').focus();
                }
            }
        });
    }
);
