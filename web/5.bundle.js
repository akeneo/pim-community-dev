webpackJsonp([5],{

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

/***/ 212:
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/tab/attributes/copy.html ***!
  \**************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<% if (!copying) { %>\n    <div class=\"AknButton AknButton--grey AknButton--small start-copying\"><%- _.__('pim_enrich.entity.product.btn.compare_translate') %></div>\n<% } else { %>\n    <div data-drop-zone=\"context-selectors\" class=\"AknButtonList AknAttributeActions-contextSelectors context-selectors\"></div>\n    <div data-drop-zone=\"other-actions\" class=\"AknAttributeActions-otherActions AknButtonList AknButtonList--right other-actions\">\n        <div data-drop-zone=\"copy-actions\" class=\"AknButtonList AknButtonList--right AknAttributeActions-copyActions copy-actions\">\n            <div class=\"AknButtonList-item AknDropdown AknDropdownButton AknDropdownButton--small selection-dropdown\">\n                <a class=\"AknDropdownButton-button\" data-toggle=\"dropdown\" href=\"#\">\n                    <%- _.__('pim_enrich.entity.product.copy.select') %>\n                    <div class=\"AknDropdownButton-caretContainer\">\n                        <span class=\"AknCaret\"></span>\n                    </div>\n                </a>\n                <ul class=\"AknDropdown-menu\">\n                    <li>\n                        <a class=\"AknDropdown-menuLink select-all\"><%- _.__('pim_enrich.entity.product.copy.all') %></a>\n                        <a class=\"AknDropdown-menuLink select-all-visible\"><%- _.__('pim_enrich.entity.product.copy.all_visible') %></a>\n                        <a class=\"AknDropdown-menuLink select-none\"><%- _.__('pim_enrich.entity.product.copy.none') %></a>\n                    </li>\n                </ul>\n            </div>\n            <div class=\"AknButtonList-item AknButton AknButton--small AknButton--apply copy\"><%- _.__('pim_enrich.entity.product.copy.copy') %></div>\n            <div class=\"AknButtonList-item AknIconButton AknIconButton--grey stop-copying\"><i class=\"icon-remove\"></i></div>\n        </div>\n    </div>\n<% } %>\n"

/***/ }),

/***/ 221:
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/attributes/copy.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Copy extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! text-loader!pim/template/form/tab/attribute/copy */ 212),
        !(function webpackMissingModule() { var e = new Error("Cannot find module \"pim/form/common/attributes/copy-field\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()),
        __webpack_require__(/*! pim/field-manager */ 147),
        __webpack_require__(/*! pim/attribute-manager */ 42),
        __webpack_require__(/*! pim/user-context */ 7),
        __webpack_require__(/*! pim/fetcher-registry */ 6)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        BaseForm,
        mediator,
        template,
        CopyField,
        FieldManager,
        AttributeManager,
        UserContext,
        FetcherRegistry
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknAttributeActions-copyActions attribute-copy-actions',
            copyFields: {},
            copying: false,
            locale: null,
            scope: null,
            scopeLabel: null,
            events: {
                'click .start-copying': 'startCopying',
                'click .stop-copying': 'stopCopying',
                'click .select-all': 'selectAll',
                'click .select-all-visible': 'selectAllVisible',
                'click .select-none': 'selectNone',
                'click .copy': 'copy'
            },

            /**
             * Configure this extension
             *
             * @returns {Promise}
             */
            configure: function () {
                this.locale = UserContext.get('catalogLocale');
                this.scope  = UserContext.get('catalogScope');
                this.getScopeLabel(this.scope).then(function (scopeLabel) {
                    this.scopeLabel = scopeLabel;
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                this.onExtensions('pim_enrich:form:scope_switcher:pre_render', this.initScope.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:pre_render', this.initLocale.bind(this));
                this.onExtensions('pim_enrich:form:scope_switcher:change', function (event) {
                    this.setScope(event.scopeCode);
                }.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:change', function (event) {
                    this.setLocale(event.localeCode);
                }.bind(this));

                return this.getScopeLabel(this.scope).then(function (scopeLabel) {
                    this.scopeLabel = scopeLabel;
                }.bind(this)).then(function () {
                    return BaseForm.prototype.configure.apply(this, arguments)
                });
            },

            /**
             * Return the values object from which data must be copied
             *
             * @returns {Object}
             */
            getSourceData: function () {
                return this.getFormData().values;
            },

            /**
             * Render the copy panel
             *
             * @returns {Object}
             */
            render: function () {
                this.trigger('comparison:change', this.copying);

                this.$el.html(this.template({'copying': this.copying}));
                if (this.copying) {
                    this.renderExtensions();
                }

                this.delegateEvents();

                return this;
            },

            /**
             * Render the copy element inside each field that can be copied
             *
             * @param {Object} event
             */
            addFieldExtension: function (event) {
                var field = event.field;
                if (this.copying && this.canBeCopied(field)) {
                    field.addElement('comparison', this.code, this.getCopyField(field));
                }
            },

            /**
             * Get or create a copy field object corresponding to the specified field
             *
             * @param {Field} field
             *
             * @returns {CopyField}
             */
            getCopyField: function (field) {
                var code = field.attribute.code;
                if (!_.has(this.copyFields, code)) {
                    var sourceData = this.getSourceData();
                    var copyField = new CopyField(field.attribute);

                    copyField.setContext({
                        locale: this.locale,
                        scope: this.scope,
                        scopeLabel: this.scopeLabel
                    });
                    copyField.setValues(sourceData[code]);
                    copyField.setField(field);

                    this.copyFields[code] = copyField;
                }

                return this.copyFields[code];
            },

            /**
             * Indicate if the specified field can be copied
             *
             * @param {Field} field
             * @returns {boolean}
             */
            canBeCopied: function (field) {
                return field.attribute.localizable || field.attribute.scopable;
            },

            /**
             * Launch the copy process for selected fields
             */
            copy: function () {
                _.each(this.copyFields, function (copyField) {
                    if (copyField.selected && copyField.field && copyField.field.isEditable()) {
                        var formValues = this.getFormModel().get('values');
                        var oldValue = AttributeManager.getValue(
                            formValues[copyField.field.attribute.code],
                            copyField.field.attribute,
                            UserContext.get('catalogLocale'),
                            UserContext.get('catalogScope')
                        );

                        oldValue.data = copyField.getCurrentValue().data;
                        this.getRoot().trigger('pim_enrich:form:entity:update_state');
                        copyField.setSelected(false);
                    }
                }.bind(this));

                this.trigger('copy:copy-fields:after');
            },

            /**
             * Enter in copy mode
             */
            startCopying: function () {
                this.copying = true;
                this.triggerContextChange();
            },

            /**
             * Close copy mode
             */
            stopCopying: function () {
                this.copying = false;
                this.triggerContextChange();
            },

            /**
             * Initialize  the locale if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initLocale: function (event) {
                if (undefined === this.getLocale()) {
                    this.setLocale(event.localeCode);
                } else {
                    event.localeCode = this.getLocale();
                }
            },

            /**
             * Change the locale for copy context
             *
             * @param {string} locale
             */
            setLocale: function (locale) {
                this.locale = locale;
                this.triggerContextChange();
            },

            /**
             * Get the current locale for copy
             *
             * @returns {string}
             */
            getLocale: function () {
                return this.locale;
            },

            /**
             * Initialize  the scope if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initScope: function (event) {
                if (undefined === this.getScope()) {
                    this.setScope(event.scopeCode);
                } else {
                    event.scopeCode = this.getScope();
                }
            },

            /**
             * Change the scope for copy context
             *
             * @param {string} scopeCode
             */
            setScope: function (scopeCode) {
                this.getScopeLabel(scopeCode).then(function (scopeLabel) {
                    this.scopeLabel = scopeLabel;
                    this.scope = scopeCode;
                    this.triggerContextChange();
                }.bind(this));
            },

            /**
             * Get the current scope for copy
             *
             * @returns {string}
             */
            getScope: function () {
                return this.scope;
            },

            /**
             * Reset copy fields cache then trigger the context change event
             */
            triggerContextChange: function () {
                this.copyFields = {};
                this.trigger('copy:context:change');
            },

            /**
             * Mark all fields (from all attribute groups) as selected
             */
            selectAll: function () {
                var fieldPromises = [];
                _.each(this.getSourceData(), function (value, attributeCode) {
                    fieldPromises.push(FieldManager.getField(attributeCode));
                }.bind(this));

                $.when.apply(this, fieldPromises)
                    .then(function () {
                        this.selectFields(arguments);
                    }.bind(this));
            },

            /**
             * Mark all visible fields (from active attribute group) as selected
             */
            selectAllVisible: function () {
                this.selectFields(FieldManager.getVisibleFields());
            },

            /**
             * Mark all fields as unselected
             */
            selectNone: function () {
                this.selectFields([]);
            },

            /**
             * Unselect all field
             */
            unselectAll: function () {
                _.each(this.copyFields, function (field) {
                    field.setSelected(false);
                });
            },

            /**
             * Mark specified fields as selected and trigger the select event
             *
             * @param {Field[]} fields
             */
            selectFields: function (fields) {
                this.unselectAll();

                _.each(fields, function (field) {
                    if (this.canBeCopied(field)) {
                        this.getCopyField(field).setSelected(true);
                    }
                }.bind(this));

                this.trigger('copy:select:after');
            },

            /**
             * Get the scope label with the given scope code
             *
             * @param {string} scopeCode
             *
             * @returns {Promise}
             */
            getScopeLabel: function (scopeCode) {
                return FetcherRegistry.getFetcher('channel').fetchAll().then(function (channels) {
                    var scope = _.findWhere(channels, { code: scopeCode });

                    return scope.label;
                });
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});