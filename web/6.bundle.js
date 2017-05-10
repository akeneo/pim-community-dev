webpackJsonp([6],{

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

/***/ 206:
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/redirect.html ***!
  \***************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<button class=\"AknButton <%- buttonClass %> AknButton--withIcon AknButtonList-item\">\n    <i class=\"AknButton-icon icon-<%- iconName %>\"></i>\n    <%- label %>\n</button>\n"

/***/ }),

/***/ 238:
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/redirect.js ***!
  \*********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! routing */ 3),
        __webpack_require__(/*! pim/router */ 12),
        __webpack_require__(/*! pim/common/property */ 149),
        __webpack_require__(/*! text-loader!pim/template/form/redirect */ 206)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, __, BaseForm, Routing, router, propertyAccessor, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click': 'redirect'
            },

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
                this.isVisible().then(function (isVisible) {
                    if (!isVisible) {
                        return this;
                    }

                    this.$el.html(this.template({
                        label: __(this.config.label),
                        iconName: this.config.iconName,
                        buttonClass: this.config.buttonClass || 'AknButton--action'
                    }));
                }.bind(this));

                return this;
            },

            /**
             * Redirect to the route given in the config
             */
            redirect: function () {
                router.redirect(this.getUrl());
            },

            /**
             * Get the route to redirect to
             *
             * @return {string}
             */
            getUrl: function () {
                var params = {};
                if (this.config.identifier) {
                    params[this.config.identifier.name] = propertyAccessor.accessProperty(
                        this.getFormData(),
                        this.config.identifier.path
                    );
                }

                return Routing.generate(this.config.route, params);
            },

            /**
             * Should this extension render
             *
             * @return {Promise}
             */
            isVisible: function () {
                return $.Deferred().resolve(true).promise();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});