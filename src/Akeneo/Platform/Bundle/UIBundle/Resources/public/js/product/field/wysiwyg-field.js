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
        'jquery',
        'pim/field',
        'underscore',
        'pim/template/product/field/textarea',
        'summernote'
    ],
    function (
        $,
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first textarea:first': 'updateModel',
                'click .note-insert': 'setStyleForLinkModal'
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
                this.$('textarea:not(.note-codable)').summernote({
                    disableResizeEditor: true,
                    height: 200,
                    iconPrefix: 'icon-',
                    toolbar: [
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link']],
                        ['view', ['codeview']]
                    ],
                    prettifyHtml: false
                })
                .on('summernote.blur', this.updateModel.bind(this))
                .on('summernote.keyup', this.removeEmptyTags.bind(this));

                this.$('.note-codable').on('blur', function () {
                    this.removeEmptyTags();
                    this.updateModel();
                }.bind(this));
            },

            removeEmptyTags: function () {
                var textarea = this.$('.field-input:first textarea:first');
                var editorHTML = $.parseHTML(textarea.code());
                var textIsEmpty = $(editorHTML).text().length === 0;

                if (textIsEmpty) {
                    textarea.code('');
                }
            },

            /**
             * @inheritDoc
             */
            updateModel: function () {
                var data = this.$('.field-input:first textarea:first').code();
                data = '<p><br></p>' === data ? this.attribute.empty_value : data;
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            },

            /**
             * @inheritDoc
             */
            setFocus: function () {
                this.$('.field-input:first .note-editable').trigger('focus');
            },

            /**
             * Places the modal backdrop in the page itself, and not outside of the body.
             * This allows the z-index to work properly in the mass-edit form, as it is
             * itself in a modal with its own z-index.
             */
            moveModalBackdrop: function () {
                $('.modal-backdrop').prependTo('.AknFullPage');
            },

            /**
             * Since 3.0, default pages includes a lot of .sticky and .fixed elements. It implies it's impossible to
             * display elements on top of the page, without setting z-index in every element of the page.
             * Here, summernote create a modal directly from the button, and this modal is hidden in the bottom of the
             * page. We override the default behavior of the modal opening, to put it in the root of the <body>, then
             * put it back once user selected its link.
             *
             * @param jqueryEvent
             */
            setStyleForLinkModal: function (jqueryEvent) {
                this.moveModalBackdrop();

                const source = jqueryEvent.originalEvent.path ?
                    $(jqueryEvent.originalEvent.path[0]) :
                    $(jqueryEvent.originalEvent.originalTarget);

                if (
                    source.hasClass('icon-link')
                   || source.hasClass('icon-edit')
                    || (source.hasClass('btn-sm') && ('showLinkDialog' === source.data('event')))
                ) {
                    const editor = source.closest('.note-editor');
                    const modal = editor.find('.note-link-dialog.modal');

                    // Set PIM style
                    modal.find('.note-link-text, .note-link-url').addClass('AknTextField');
                    modal.find('label').addClass('AknFieldContainer-label');
                    modal.find('.form-group.row').addClass('AknFieldContainer');
                    modal.find('.modal-title').addClass('AknFullPage-subTitle');
                    modal.find('.btn.btn-primary.note-link-btn').addClass('AknButton AknButton--apply');
                    modal.find('.modal-footer').addClass('AknButtonList AknButtonList--single');
                    modal.find('.close').addClass('AknFullPage-cancel');

                    // Move Dialog to <body>
                    const oreviousParent = modal.parent();
                    modal.appendTo('body');
                    modal.one('hidden.bs.modal', function () {
                        modal.appendTo(oreviousParent);
                    });
                }
            }
        });
    }
);
