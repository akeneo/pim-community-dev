webpackJsonp([21],{

/***/ 198:
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/index/confirm-button.html ***!
  \***************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a class=\"AknButton AknButton--withIcon <%- buttonClass %>\" title=\"<%- buttonLabel %>\" data-title=\" <%- title %>\"\n   data-dialog=\"confirm\" data-method=\"POST\"\n   data-message=\"<%- message %>\" data-url=\"<%- url %>\" data-redirect-url=\"<%- redirectUrl %>\"\n   data-error-message=\"<%- errorMessage %>\" data-success-message=\"<%- successMessage %>\">\n    <i class=\"AknButton-icon icon-<%- iconName %>\"></i>\n    <%- buttonLabel %>\n</a>\n"

/***/ }),

/***/ 228:
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/index/confirm-button.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Confirm button extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! routing */ 3),
        __webpack_require__(/*! text-loader!pim/template/form/index/confirm-button */ 198)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        Routing,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config || {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonClass: this.config.buttonClass,
                    buttonLabel: __(this.config.buttonLabel),
                    title: __(this.config.title),
                    message: __(this.config.message),
                    url: Routing.generate(this.config.url),
                    redirectUrl: Routing.generate(this.config.redirectUrl),
                    errorMessage: __(this.config.errorMessage),
                    successMessage: __(this.config.successMessage),
                    iconName: this.config.iconName
                }));

                this.renderExtensions();

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});