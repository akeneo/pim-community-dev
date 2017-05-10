webpackJsonp([20],{

/***/ 200:
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/index/index.html ***!
  \******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<header class=\"AknTitleContainer navigation\">\n    <div class=\"AknTitleContainer-backContainer\" data-drop-zone=\"back\">\n    </div>\n    <div class=\"AknTitleContainer-contentContainer\">\n        <div class=\"AknTitleContainer-mainLine\">\n            <div class=\"AknTitleContainer-titleContainer\" data-drop-zone=\"title\">\n                <h2 class=\"AknTitleContainer-title\">\n                    <%- title %>\n                </h2>\n            </div>\n            <div class=\"AknTitleContainer-rightButtons\" data-drop-zone=\"buttons\">\n            </div>\n        </div>\n    </div>\n</header>\n<div data-drop-zone=\"content\">\n</div>\n"

/***/ }),

/***/ 231:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/index/index.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Index extension for any basic screen with grid
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/index/index */ 200)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
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
            render: function () {
                this.$el.html(this.template({
                    title: __(this.config.title)
                }));

                this.renderExtensions();

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});