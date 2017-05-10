webpackJsonp([28],{

/***/ 190:
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/add-select/line.html ***!
  \**********************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"select2-result-label-attribute\">\n    <input data-code=\"<%- item.id %>\" type=\"checkbox\" <%- checked ? 'checked=\"checked\"' : '' %> />\n    <span class=\"attribute-label\"><%- item.text %></span>\n</div>\n"

/***/ }),

/***/ 216:
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/add-select/line.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Common add select line view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! text-loader!pim/template/form/add-select/line */ 190)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        template
    ) {
        return Backbone.View.extend({
            className: '.select2-results',
            template: _.template(template),
            checked: false,
            item: null,

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.item = this.options.item;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    item:    this.item,
                    checked: this.checked
                }));

                return this;
            },

            /**
             * Update the checkbox status then render the view
             *
             * @param {bool} checked
             */
            setCheckedCheckbox: function (checked) {
                this.checked = checked;

                this.render();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});