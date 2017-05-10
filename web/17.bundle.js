webpackJsonp([17],{

/***/ 204:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/properties/general.html ***!
  \*************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"tabsection-title\">\n    <%- sectionTitle %>\n</div>\n<div class=\"tabsection-content\">\n    <div class=\"AknFormContainer AknFormContainer--withPadding\">\n        <div class=\"AknFieldContainer\">\n            <div class=\"AknFieldContainer-header\">\n                <label class=\"control-label required\" for=\"<%- inputField %>\">\n                    <%- codeLabel %> <em><%- formRequired %></em>\n                </label>\n            </div>\n            <div class=\"AknFieldContainer-inputContainer\">\n                <input id=\"<%- inputField %>\" class=\"AknTextField\" type=\"text\" readonly disabled required value=\"<%- model.code %>\">\n            </div>\n        </div>\n    </div>\n</div>\n"

/***/ }),

/***/ 236:
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/properties/general.js ***!
  \*******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Module used to display the generals properties of an entity type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! pim/fetcher-registry */ 6),
        __webpack_require__(/*! text-loader!pim/template/form/properties/general */ 204),
        __webpack_require__(/*! jquery.select2 */ 29)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                var config = this.options.config;

                this.$el.html(this.template({
                    model: this.getFormData(),
                    sectionTitle: __(config.sectionTitle),
                    codeLabel: __(config.codeLabel),
                    formRequired: __(config.formRequired),
                    inputField: config.inputField
                }));

                this.$el.find('select.select2').select2({});

                this.renderExtensions();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});