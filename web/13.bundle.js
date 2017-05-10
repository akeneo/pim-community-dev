webpackJsonp([13],{

/***/ 213:
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/tab/properties.html ***!
  \*********************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"tabsections\" data-drop-zone=\"accordion\"></div>\n"

/***/ }),

/***/ 244:
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/tab/properties.js ***!
  \***************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Module used to display a simple properties tab
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! pim/fetcher-registry */ 6),
        __webpack_require__(/*! text-loader!pim/template/form/tab/properties */ 213),
        __webpack_require__(/*! jquery.select2 */ 29)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'AknTabContainer-content properties',
            template: _.template(template),

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
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __(this.config.label)
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.renderExtensions();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});