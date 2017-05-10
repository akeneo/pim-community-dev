webpackJsonp([12],{

/***/ 149:
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/common/property.js ***!
  \****************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Property accessor extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = function () {
    return {
        /**
         * Access a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {mixed}  defaultValue
         *
         * @return {mixed}
         */
        accessProperty: function (data, path, defaultValue) {
            defaultValue = defaultValue || null;
            var pathPart = path.split('.');

            if (undefined === data[pathPart[0]]) {
                return defaultValue;
            }

            return 1 === pathPart.length ?
                data[pathPart[0]] :
                this.accessProperty(data[pathPart[0]], pathPart.slice(1).join('.'), defaultValue);
        },

        /**
         * Update a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {mixed}  value
         *
         * @return {mixed}
         */
        updateProperty: function (data, path, value) {
            var pathPart = path.split('.');

            data[pathPart[0]] = 1 === pathPart.length ?
                value :
                this.updateProperty(data[pathPart[0]], pathPart.slice(1).join('.'), value);

            return data;
        }
    };
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 194:
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/download-file.html ***!
  \********************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a class=\"AknButton AknButton--grey AknButton--withIcon btn-download AknButtonList-item\" href=\"<%- url %>\">\n    <i class=\"AknButton-icon icon-<%- btnIcon %>\"></i>\n    <%- btnLabel %>\n</a>\n"

/***/ }),

/***/ 224:
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/download-file.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Download file extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/download-file */ 194),
        __webpack_require__(/*! routing */ 3),
        __webpack_require__(/*! pim/user-context */ 7),
        __webpack_require__(/*! pim/common/property */ 149)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_,
              __,
              BaseForm,
              template,
              Routing,
              UserContext,
              propertyAccessor
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.isVisible()) {
                    return this;
                }
                this.$el.html(this.template({
                    btnLabel: __(this.config.label),
                    btnIcon: this.config.iconName,
                    url: this.getUrl()
                }));

                return this;
            },

            /**
             * Get the url with parameters
             *
             * @returns {string}
             */
            getUrl: function () {
                var parameters = {};
                if (this.config.urlParams) {
                    var formData = this.getFormData();
                    this.config.urlParams.forEach(function (urlParam) {
                        parameters[urlParam.property] =
                            propertyAccessor.accessProperty(formData, urlParam.path);
                    });
                }

                return Routing.generate(
                    this.config.url,
                    parameters
                );
            },

            /**
             * Returns true if the extension should be visible
             *
             * @returns {boolean}
             */
            isVisible: function () {
                return propertyAccessor.accessProperty(this.getFormData(), this.config.isVisiblePath);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});