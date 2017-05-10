webpackJsonp([11],{

/***/ 147:
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field-manager.js ***!
  \**********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Field manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! pim/fetcher-registry */ 6), __webpack_require__(/*! pim/form-config-provider */ 27)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, FetcherRegistry, ConfigProvider) {
        var fields = {};
        var visibleFields = {};
        var loadedModules = {};
        var getFieldForAttribute = function (attribute) {
            var deferred = $.Deferred();

            if (loadedModules[attribute.field_type]) {
                deferred.resolve(loadedModules[attribute.field_type]);

                return deferred.promise();
            }

            ConfigProvider.getAttributeFields().done(function (attributeFields) {
                var fieldModule = attributeFields[attribute.field_type];

                if (!fieldModule) {
                    throw new Error('No field defined for attribute type "' + attribute.field_type + '"');
                }

                __webpack_require__.e/* require */(30).then(function() { var __WEBPACK_AMD_REQUIRE_ARRAY__ = [!(function webpackMissingModule() { var e = new Error("Cannot find module \".\""); e.code = 'MODULE_NOT_FOUND'; throw e; }())]; (function (Field) {
                    loadedModules[attribute.field_type] = Field;
                    deferred.resolve(Field);
                }.apply(null, __WEBPACK_AMD_REQUIRE_ARRAY__));}).catch(__webpack_require__.oe);
            });

            return deferred.promise();
        };

        return {
            getField: function (attributeCode) {
                var deferred = $.Deferred();

                if (fields[attributeCode]) {
                    deferred.resolve(fields[attributeCode]);

                    return deferred.promise();
                }

                FetcherRegistry.getFetcher('attribute').fetch(attributeCode).done(function (attribute) {
                    getFieldForAttribute(attribute).done(function (Field) {
                        fields[attributeCode] = new Field(attribute);
                        deferred.resolve(fields[attributeCode]);
                    });
                });

                return deferred.promise();
            },
            getNotReadyFields: function () {
                var notReadyFields = [];

                _.each(fields, function (field) {
                    if (!field.isReady()) {
                        notReadyFields.push(field);
                    }
                });

                return notReadyFields;
            },
            getFields: function () {
                return fields;
            },
            addVisibleField: function (attributeCode) {
                visibleFields[attributeCode] = fields[attributeCode];
            },
            getVisibleFields: function () {
                return visibleFields;
            },
            getVisibleField: function (attributeCode) {
                return visibleFields[attributeCode];
            },
            clearFields: function () {
                fields = {};
            },
            clearVisibleFields: function () {
                visibleFields = {};
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 195:
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/edit-form.html ***!
  \****************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"entity-edit-form edit-form\">\n    <div data-drop-zone=\"sequential\"></div>\n    <header class=\"AknTitleContainer\">\n        <div class=\"AknTitleContainer-backContainer\" data-drop-zone=\"back\"></div>\n        <div class=\"AknTitleContainer-contentContainer\">\n            <div class=\"AknTitleContainer-mainLine\">\n                <div class=\"AknTitleContainer-titleContainer\" data-drop-zone=\"title\">\n                    <div class=\"AknTitleContainer-titleButtons AknButtonList\" data-drop-zone=\"title-buttons\"></div>\n                </div>\n                <div class=\"AknTitleContainer-rightButtons\" data-drop-zone=\"buttons\"></div>\n            </div>\n            <div class=\"AknTitleContainer-metaLine\">\n                <div class=\"AknTitleContainer-meta\" data-drop-zone=\"meta\"></div>\n                <div class=\"AknTitleContainer-state\" data-drop-zone=\"state\"></div>\n            </div>\n        </div>\n    </header>\n    <div data-drop-zone=\"content\" class=\"content\">\n    </div>\n</div>\n"

/***/ }),

/***/ 225:
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/edit-form.js ***!
  \**********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Edit form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alps <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! module-config */ 15),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! text-loader!pim/template/form/edit-form */ 195),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! pim/fetcher-registry */ 6),
        __webpack_require__(/*! pim/field-manager */ 147)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        module,
        _,
        __,
        Backbone,
        template,
        BaseForm,
        mediator,
        FetcherRegistry,
        FieldManager
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.clear('pim_enrich:form');
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

                this.onExtensions('save-buttons:register-button', function (button) {
                    this.getExtension('save-buttons').trigger('save-buttons:add-button', button);
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.getRoot().trigger('pim_enrich:form:render:before');

                this.$el.html(this.template());

                this.renderExtensions();

                this.getRoot().trigger('pim_enrich:form:render:after');
            },

            /**
             * Clear the mediator
             */
            unbindEvents: function () {
                mediator.clear('pim_enrich:form');
            },

            /**
             * Clear the cached informations
             */
            clearCache: function () {
                FetcherRegistry.clearAll();
                FieldManager.clearFields();
                this.render();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});