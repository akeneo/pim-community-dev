webpackJsonp([29],{

/***/ 189:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/add-select/footer.html ***!
  \************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"ui-multiselect-footer\">\n    <span class=\"item-counter\"><%- _.__(countTitle, {'itemsCount': numberOfItems}) %></span>\n    <button class=\"AknButton AknButton--small AknButton--apply AknButton--withIcon\" type=\"button\">\n        <i class=\"AknButton-icon icon-plus\"></i>\n        <%- buttonTitle %>\n    </button>\n</div>\n"

/***/ }),

/***/ 215:
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/add-select/footer.js ***!
  \******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Common add select footer view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! text-loader!pim/template/form/add-select/footer */ 189)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            buttonTitle: null,
            numberOfItems: 0,
            countTitle: null,
            addEvent: null,

            events: {
                'click button': 'onAdd'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.buttonTitle   = this.options.buttonTitle;
                this.countTitle    = this.options.countTitle;
                this.addEvent      = this.options.addEvent;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonTitle: this.buttonTitle,
                    numberOfItems: this.numberOfItems,
                    countTitle: this.countTitle
                }));

                return this;
            },

            /**
             * Update the item counter line and re-render the view.
             *
             * @param {int|string} number
             */
            updateNumberOfItems: function (number) {
                this.numberOfItems = number;

                this.render();
            },

            /**
             * Method called when the 'add' button is clicked
             */
            onAdd: function () {
                this.trigger(this.addEvent);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});