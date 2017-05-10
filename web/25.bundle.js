webpackJsonp([25],{

/***/ 193:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/delete.html ***!
  \*************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<i class=\"AknButton-icon icon-trash\"></i>\n<%- _.__('pim_enrich.entity.product.btn.delete') %>\n"

/***/ }),

/***/ 223:
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/delete.js ***!
  \*******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Delete extension
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/delete */ 193),
        __webpack_require__(/*! pim/router */ 12),
        __webpack_require__(/*! oro/loading-mask */ 19),
        __webpack_require__(/*! oro/messenger */ 21),
        __webpack_require__(/*! pim/dialog */ 13)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        template,
        router,
        LoadingMask,
        messenger,
        Dialog
    ) {
        return BaseForm.extend({
            tagName: 'button',
            className: 'AknButton AknButton--important AknButton--withIcon AknTitleContainer-rightButton delete',
            template: _.template(template),
            events: {
                'click': 'delete'
            },

            /**
             * The remover should be injected / overridden by the concrete implementation
             * It is an object that define a remove function
             */
            remover: {
                remove: function () {
                    throw 'Remove function should be implemented in remover';
                }
            },

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, {
                    trans: {
                        title: 'confirmation.remove.item',
                        content: 'pim_enrich.confirmation.delete_item',
                        success: 'flash.item.removed',
                        fail: 'error.removing.item'
                    },
                    redirect: 'oro_default'
                }, meta.config);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({'__': __}));
                this.delegateEvents();

                return this;
            },

            /**
             * Open a dialog to ask the user to confirm the deletion
             */
            delete: function () {
                Dialog.confirm(
                    __(this.config.trans.title),
                    __(this.config.trans.content),
                    this.doDelete.bind(this)
                );
            },

            /**
             * Send a request to the backend in order to delete the element
             */
            doDelete: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                this.remover.remove(this.getIdentifier())
                    .done(function () {
                        messenger.notificationFlashMessage('success', __(this.config.trans.success));
                        router.redirectToRoute(this.config.redirect);
                    }.bind(this))
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            __(this.config.trans.failed);

                        messenger.notificationFlashMessage('error', message);
                    })
                    .always(function () {
                        loadingMask.hide().$el.remove();
                    });
            },

            /**
             * Get the current form identifier
             *
             * @return {String}
             */
            getIdentifier: function () {
                return this.getFormData().code;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});