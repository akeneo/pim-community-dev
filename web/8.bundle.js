webpackJsonp([8],{

/***/ 199:
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/index/create-button.html ***!
  \**************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a id=\"create-button-extension\" class=\"AknButton AknButton--apply AknButton--withIcon AknButtonList-item\" data-form-url=\"<%- url %>\">\n    <i class=\"AknButton-icon icon-<%- iconName %>\"></i>\n    <%- title %>\n</a>\n"

/***/ }),

/***/ 229:
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/index/create-button.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Create button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/index/create-button */ 199),
        __webpack_require__(/*! routing */ 3),
        __webpack_require__(/*! pim/dialogform */ 250)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        __,
        BaseForm,
        template,
        Routing,
        DialogForm
    ) {
        return BaseForm.extend({
            template: _.template(template),
            dialog: null,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    title: __(this.config.title),
                    iconName: this.config.iconName,
                    url: Routing.generate(this.config.url)
                }));

                this.dialog = new DialogForm('#create-button-extension');

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 250:
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/pim-dialogform.js ***!
  \***********************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global console */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! oro/mediator */ 5), __webpack_require__(/*! pim/router */ 12), __webpack_require__(/*! oro/loading-mask */ 19), __webpack_require__(/*! pim/initselect2 */ 28), __webpack_require__(/*! jquery-ui */ 47), __webpack_require__(/*! bootstrap */ 23)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, mediator, router, LoadingMask, initSelect2) {
        'use strict';

        // Allow using select2 search box in jquery ui dialog
        $.ui.dialog.prototype._allowInteraction = function (e) {
            return !!$(e.target).closest('.ui-dialog, .select2-drop').length;
        };

        return function (elementId, callback) {
            var $el = $(elementId);
            if (!$el.length) {
                return console.error('DialogForm: the element could not be found!');
            }
            var $dialog;
            var url = $el.attr('data-form-url');
            if (!url) {
                throw new Error('DialogForm: please specify the url');
            }
            var width = $el.attr('data-form-width') || 400;

            var loadingMask = null;

            function showLoadingMask() {
                if (!loadingMask) {
                    loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo($('#container'));
                }
                loadingMask.show();
            }

            function destroyDialog() {
                if ($dialog && $dialog.length) {
                    $dialog.remove();
                }
                $dialog = null;
            }

            function createDialog(data) {
                destroyDialog();
                var $form = $(data);
                var formTitle = $form.data('title');
                var formId = '#' + $form.attr('id');

                var formButtons = [];
                var submitButton = $form.data('button-submit');
                var cancelButton = $form.data('button-cancel');
                if (submitButton) {
                    formButtons.push({
                        text: submitButton,
                        'class': 'btn btn-primary',
                        click: function () {
                            showLoadingMask();
                            $.ajax({
                                url: url,
                                type: 'post',
                                data: $(formId).serialize(),
                                success: function (data) {
                                    processResponse(data);
                                    mediator.trigger('dialog:open:after', this);
                                }
                            });
                        }
                    });
                }
                if (cancelButton) {
                    formButtons.push({
                        text: cancelButton,
                        'class': 'btn',
                        click: function () {
                            destroyDialog();
                        }
                    });
                }

                $dialog = $form.dialog({
                    title: formTitle,
                    modal: true,
                    resizable: false,
                    width: width,
                    buttons: formButtons,
                    open: function () {
                        $(this).parent().keypress(function (e) {
                            if (e.keyCode === $.ui.keyCode.ENTER) {
                                e.preventDefault();
                                e.stopPropagation();
                                $(this).find('button.btn-primary:eq(0)').click();
                            }
                        });
                    },
                    close: function () {
                        $(this).remove();
                    }
                });

                initSelect2.init($(formId));
                $(formId + ' .switch').bootstrapSwitch();

                $(formId).find('[data-toggle="tooltip"]').tooltip();
            }

            function isJSON(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }

                return true;
            }

            function processResponse(data) {
                loadingMask.hide();
                if (isJSON(data)) {
                    data = $.parseJSON(data);
                    destroyDialog();
                    if (callback) {
                        callback(data);
                    } else {
                        router.redirect(data.url);
                    }
                } else if ($(data).prop('tagName').toLowerCase() === 'form') {
                    createDialog(data);
                }
            }

            $el.on('click', function (e) {
                e.preventDefault();
                showLoadingMask();
                $.ajax({
                    url: url,
                    type: 'get',
                    success: function (data) {
                        loadingMask.hide();
                        createDialog(data);
                        mediator.trigger('dialog:open:after', this);
                    }
                });
            });
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});