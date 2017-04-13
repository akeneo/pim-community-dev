'use strict';
/**
 * Wysiwyg field
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
        'text!pim/template/product/field/textarea',
        'summernote'
    ],
    function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first textarea:first': 'updateModel'
            },

            /**
             * @inheritDoc
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * @inheritDoc
             */
            postRender: function () {
                this.$('textarea').summernote({
                    disableResizeEditor: true,
                    height: 200,
                    iconPrefix: 'icon-',
                    toolbar: [
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link']],
                        ['view', ['codeview']]
                    ],
                    callbacks: {},
                })
                .on('summernote.blur', this.updateModel.bind(this))
                .on('summernote.keyup', this.cleanEmptyInput.bind(this));

                this.$('.note-codable').on('blur', function () {
                    this.cleanEmptyInput();
                    this.updateModel();
                }.bind(this));
            },

            cleanEmptyInput: function () {
                var isEmpty = $.summernote.core.dom.isEmpty;
                var textarea = this.$('.field-input:first textarea:first');
                var editorElement = this.$('.note-editable').get(0);
                var editorCode = $.parseHTML(textarea.code());
                var textIsEmpty = $(editorCode).text().length === 0;

                if (isEmpty(editorElement) || textIsEmpty) {
                    textarea.code('');
                }
            },

            /**
             * @inheritDoc
             */
            updateModel: function () {
                var data = this.$('.field-input:first textarea:first').code();
                data = '' === data ? this.attribute.empty_value : data;
                this.setCurrentValue(data);
            },

            /**
             * @inheritDoc
             */
            setFocus: function () {
                this.$('.field-input:first .note-editable').trigger('focus');
            }
        });
    }
);
