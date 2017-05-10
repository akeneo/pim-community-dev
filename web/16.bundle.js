webpackJsonp([16],{

/***/ 205:
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/properties/translation.html ***!
  \*****************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"tabsection-title\">\n    <%- _.__(label) %>\n</div>\n<div class=\"tabsection-content\">\n    <div class=\"AknFormContainer AknFormContainer--withPadding\">\n        <% _.each(locales, function (locale) { %>\n            <div class=\"AknFieldContainer\">\n                <div class=\"AknFieldContainer-header\">\n                    <label class=\"AknFieldContainer-label\" for=\"<%- fieldBaseId %><%- locale.code %>\">\n                        <%= locale.label %>\n                    </label>\n                </div>\n                <div class=\"AknFieldContainer-inputContainer field-input\">\n                    <input id=\"<%- fieldBaseId %><%- locale.code %>\"\n                           class=\"AknTextField label-field\"\n                           type=\"text\"\n                           data-locale=\"<%- locale.code %>\"\n                           value=\"<%- model.labels[locale.code] %>\"\n                           <%- isReadOnly ? 'readonly disabled' : '' %>\n                    >\n                </div>\n                <% if (errors[locale.code]) { %>\n                    <div class=\"AknFieldContainer-footer\">\n                        <span class=\"validation-error\">\n                            <i class=\"icon-warning-sign\"></i>\n                            <span class=\"error-message\"><%- errors[locale.code].message %></span>\n                        </span>\n                    </div>\n                <% } %>\n            </div>\n        <% }) %>\n    </div>\n</div>\n"

/***/ }),

/***/ 237:
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/properties/translation.js ***!
  \***********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Module used to display the localized properties of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! pim/fetcher-registry */ 6),
        __webpack_require__(/*! text-loader!pim/template/form/properties/translation */ 205)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection translation-container',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },
            validationErrors: {},
            locales: null,

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
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:pre_save',
                    this.onPreSave
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:bad_request',
                    this.onValidationError
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:locales_updated',
                    this.onLocalesUpdated.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Pre save callback
             */
            onPreSave: function () {
                this.validationErrors = {};

                this.render();
            },

            /**
             * On validation callback
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.validationErrors = event.response.translations ? event.response.translations : {};

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.locales) {
                    FetcherRegistry.getFetcher('locale')
                        .search({'activated': true, 'cached': true})
                        .then(function (locales) {
                            this.locales = locales;
                            this.render();
                        }.bind(this));
                }

                this.$el.html(this.template({
                    model: this.getFormData(),
                    locales: this.locales,
                    errors: this.validationErrors,
                    label: this.config.label,
                    fieldBaseId: this.config.fieldBaseId,
                    isReadOnly: false /* false as default default value */
                }));

                this.delegateEvents();

                this.renderExtensions();
            },

            /**
             * @param {Object} event
             */
            updateModel: function (event) {
                var data = this.getFormData();

                if (Array.isArray(data.labels)) {
                    data.labels = {};
                }

                data.labels[event.target.dataset.locale] = event.target.value;

                this.setData(data);
            },

            /**
             * Updates locales if were updated
             */
            onLocalesUpdated: function () {
                FetcherRegistry.getFetcher('locale')
                    .search({'activated': true, 'cached': false})
                    .then(function (locales) {
                        if (!_.isEqual(this.locales, locales)) {
                            this.locales = locales;

                            return this.render();
                        }

                    }.bind(this));
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});