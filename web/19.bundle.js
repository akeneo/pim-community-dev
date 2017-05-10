webpackJsonp([19],{

/***/ 201:
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/meta/created.html ***!
  \*******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<span title=\"<%- label %>: <%- loggedAt %> <%- labelBy %> <%- author %>\">\n    <%- label %>: <%- loggedAt %> <%- labelBy %> <%- author %>\n</span>\n"

/***/ }),

/***/ 233:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/meta/created.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__; 
/**
 * Created at extension
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
        __webpack_require__(/*! text-loader!pim/template/form/meta/created */ 201)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, __, BaseForm, formTemplate) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'AknTitleContainer-metaItem',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                this.label   = __(this.config.label);
                this.labelBy = __(this.config.labelBy);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var product = this.getFormData();
                var html = '';

                if (product.meta.created) {
                    html = this.template({
                        label: this.label,
                        labelBy: this.labelBy,
                        loggedAt: _.result(product.meta.created, 'logged_at', null),
                        author: _.result(product.meta.created, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});