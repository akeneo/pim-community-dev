webpackJsonp([26],{

/***/ 192:
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/back-to-grid.html ***!
  \*******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<i class=\"icon-chevron-left\"></i>\n"

/***/ }),

/***/ 222:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/back-to-grid.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Back to grid extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/back-to-grid */ 192),
        __webpack_require__(/*! pim/router */ 12),
        __webpack_require__(/*! pim/user-context */ 7)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, __, BaseForm, template, router, UserContext) {
        return BaseForm.extend({
            tagName: 'span',
            events: {
                'click': 'backToGrid'
            },
            className: 'AknTitleContainer-backLink back-link',
            template: _.template(template),
            config: {},
            attributes: {
                title: __('pim_enrich.navigation.link.back_to_grid')
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                return this;
            },

            backToGrid: function () {
                router.redirectToRoute(
                    this.config.backUrl,
                    {
                        dataLocale: UserContext.get('catalogLocale')
                    }
                );
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});