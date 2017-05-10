webpackJsonp([4],{

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

/***/ 152:
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/manager/attribute-group-manager.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! pim/fetcher-registry */ 6), __webpack_require__(/*! pim/attribute-manager */ 42)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, FetcherRegistry, AttributeManager) {
    return {
        /**
         * Get all the attribute group for the given object
         *
         * @param {Object} object
         *
         * @return {Promise}
         */
        getAttributeGroupsForObject: function (object) {
            return $.when(
                FetcherRegistry.getFetcher('attribute-group').fetchAll(),
                AttributeManager.getAttributes(object)
            ).then(function (attributeGroups, ObjectAttributes) {
                var activeAttributeGroups = {};
                _.each(attributeGroups, function (attributeGroup) {
                    if (_.intersection(attributeGroup.attributes, ObjectAttributes).length > 0) {
                        activeAttributeGroups[attributeGroup.code] = attributeGroup;
                    }
                });

                return activeAttributeGroups;
            });
        },

        /**
         * Get attribute group values filtered from the whole list
         *
         * @param {Object} values
         * @param {String} attributeGroup
         *
         * @return {Object}
         */
        getAttributeGroupValues: function (values, attributeGroup) {
            var matchingValues = {};
            if (!attributeGroup) {
                return matchingValues;
            }

            _.each(attributeGroup.attributes, function (attributeCode) {
                if (values[attributeCode]) {
                    matchingValues[attributeCode] = values[attributeCode];
                }
            });

            return matchingValues;
        },

        /**
         * Get the attribute group for the given attribute
         *
         * @param {Array} attributeGroups
         * @param {String} attributeCode
         *
         * @return {String}
         */
        getAttributeGroupForAttribute: function (attributeGroups, attributeCode) {
            var result = null;

            _.each(attributeGroups, function (attributeGroup) {
                if (-1 !== attributeGroup.attributes.indexOf(attributeCode)) {
                    result = attributeGroup.code;
                }
            });

            return result;
        }
    };
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 209:
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/tab/attributes.html ***!
  \*********************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div data-drop-zone=\"sidebar\" class=\"sidebar\"></div>\n<div class=\"AknTabContainer-content tab-content\">\n    <header data-drop-zone=\"header\" class=\"AknTabHeader AknAttributeActions tab-header attribute-actions\">\n        <div data-drop-zone=\"edit-actions\" class=\"AknAttributeActions-editActions attribute-edit-actions\">\n            <div data-drop-zone=\"context-selectors\" class=\"AknButtonList AknAttributeActions-contextSelectors context-selectors\"></div>\n            <div data-drop-zone=\"other-actions\" class=\"AknAttributeActions-otherActions AknButtonList AknButtonList--right other-actions\"></div>\n        </div>\n    </header>\n    <div class=\"tab-pane active object-values\"></div>\n</div>\n"

/***/ }),

/***/ 218:
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/attributes.js ***!
  \***********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__; 
/**
 * Attribute tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! routing */ 3),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! pim/field-manager */ 147),
        __webpack_require__(/*! pim/fetcher-registry */ 6),
        __webpack_require__(/*! pim/attribute-manager */ 42),
        __webpack_require__(/*! pim/attribute-group-manager */ 152),
        __webpack_require__(/*! pim/user-context */ 7),
        __webpack_require__(/*! pim/security-context */ 31),
        __webpack_require__(/*! text-loader!pim/template/form/tab/attributes */ 209),
        __webpack_require__(/*! pim/dialog */ 13),
        __webpack_require__(/*! oro/messenger */ 21)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        mediator,
        Routing,
        BaseForm,
        FieldManager,
        FetcherRegistry,
        AttributeManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate,
        Dialog,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left object-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            rendering: false,

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
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__(this.config.tabTitle)
                });

                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:show_attribute', this.showAttribute);

                FieldManager.clearFields();

                this.onExtensions('comparison:change', this.comparisonChange.bind(this));
                this.onExtensions('group:change', this.render.bind(this));
                this.onExtensions('add-attribute:add', this.addAttributes.bind(this));
                this.onExtensions('copy:copy-fields:after', this.render.bind(this));
                this.onExtensions('copy:select:after', this.render.bind(this));
                this.onExtensions('copy:context:change', this.render.bind(this));
                this.onExtensions('pim_enrich:form:scope_switcher:pre_render', this.initScope.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:pre_render', this.initLocale.bind(this));
                this.onExtensions('pim_enrich:form:scope_switcher:change', function (event) {
                    this.setScope(event.scopeCode);
                }.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:change', function (event) {
                    this.setLocale(event.localeCode);
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;
                this.$el.html(this.template({}));
                this.getConfig().then(function () {
                    var object = this.getFormData();
                    AttributeManager.getValues(object)
                        .then(function (values) {
                            var attributeGroupValues = AttributeGroupManager.getAttributeGroupValues(
                                values,
                                this.getExtension('attribute-group-selector').getCurrentElement()
                            );

                            var fieldPromises = [];
                            _.each(attributeGroupValues, function (value, attributeCode) {
                                fieldPromises.push(this.renderField(object, attributeCode, value));
                            }.bind(this));

                            this.rendering = false;

                            return $.when.apply($, fieldPromises);
                        }.bind(this)).then(function () {
                            return _.sortBy(arguments, function (field) {
                                return field.attribute.sort_order;
                            });
                        }).then(function (fields) {
                            var $valuesPanel = this.$('.object-values');
                            $valuesPanel.empty();

                            FieldManager.clearVisibleFields();
                            _.each(fields, function (field) {
                                if (field.canBeSeen()) {
                                    field.render();
                                    FieldManager.addVisibleField(field.attribute.code);
                                    $valuesPanel.append(field.$el);
                                }
                            }.bind(this));
                        }.bind(this));
                    this.delegateEvents();

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Render a single field
             *
             * @param {Object} object
             * @param {String} attributeCode
             * @param {Array} values
             *
             * @return {Promise}
             */
            renderField: function (object, attributeCode, values) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    return $.when(
                        (new $.Deferred().resolve(field)),
                        FetcherRegistry.getFetcher('channel').fetchAll(),
                        AttributeManager.isOptional(field.attribute, object)
                    );
                }).then(function (field, channels, isOptional) {
                    var scope = _.findWhere(channels, { code: UserContext.get('catalogScope') });

                    field.setContext({
                        locale: UserContext.get('catalogLocale'),
                        scope: scope.code,
                        scopeLabel: scope.label,
                        uiLocale: UserContext.get('catalogLocale'),
                        optional: isOptional,
                        removable: SecurityContext.isGranted(this.config.removeAttributeACL)
                    });
                    field.setValues(values);

                    return field;
                }.bind(this));
            },

            /**
             * Get the configuration needed to load the attribute tab
             *
             * @return {Promise}
             */
            getConfig: function () {
                var promises = [];
                var object = this.getFormData();

                promises.push(AttributeGroupManager.getAttributeGroupsForObject(object)
                    .then(function (attributeGroups) {
                        this.getExtension('attribute-group-selector').setElements(
                            _.indexBy(_.sortBy(attributeGroups, 'sort_order'), 'code')
                        );
                    }.bind(this))
                );

                return $.when.apply($, promises).promise();
            },

            /**
             * Add an attribute to the current attribute list
             *
             * @param {Event} event
             */
            addAttributes: function (event) {
                var attributeCodes = event.codes;

                $.when(
                    FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes),
                    FetcherRegistry.getFetcher('locale').fetchActivated(),
                    FetcherRegistry.getFetcher('channel').fetchAll(),
                    FetcherRegistry.getFetcher('currency').fetchAll()
                ).then(function (attributes, locales, channels, currencies) {
                    var formData = this.getFormData();

                    _.each(attributes, function (attribute) {
                        if (!formData.values[attribute.code]) {
                            formData.values[attribute.code] = AttributeManager.generateMissingValues(
                                [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        }
                    });

                    this.getExtension('attribute-group-selector').setCurrent(
                        _.first(attributes).group_code
                    );

                    this.setData(formData);

                    this.getRoot().trigger('pim_enrich:form:add-attribute:after');
                }.bind(this));
            },

            /**
             * Remove an attribute from the collection
             *
             * @param {Event} event
             */
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted(this.config.removeAttributeACL)) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var formData = this.getFormData();
                var fields = FieldManager.getFields();

                Dialog.confirm(
                    _.__('pim_enrich.confirmation.delete.attribute'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    function () {
                        FetcherRegistry.getFetcher('attribute').fetch(attributeCode).then(function (attribute) {
                            $.ajax({
                                type: 'DELETE',
                                url: this.generateRemoveAttributeUrl(attribute),
                                contentType: 'application/json'
                            }).then(function () {
                                this.triggerExtensions('add-attribute:update:available-attributes');

                                delete formData.values[attributeCode];
                                delete fields[attributeCode];

                                this.setData(formData);

                                this.getRoot().trigger('pim_enrich:form:remove-attribute:after');

                                this.render();
                            }.bind(this)).fail(function () {
                                messenger.notificationFlashMessage(
                                    'error',
                                    _.__(this.config.deletionFailed)
                                );
                            });
                        }.bind(this));
                    }.bind(this)
                );
            },

            /**
             * Generate the remove attribute url
             *
             * @return {String}
             */
            generateRemoveAttributeUrl: function (attribute) {
                return Routing.generate(
                    this.config.removeAttributeRoute,
                    {
                        code: this.getFormData().code,
                        attributeId: attribute.id
                    }
                );
            },

            /**
             * Initialize  the scope if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initScope: function (event) {
                if (undefined === this.getScope()) {
                    this.setScope(event.scopeCode, {silent: true});
                } else {
                    event.scopeCode = this.getScope();
                }
            },

            /**
             * Set the current scope
             *
             * @param {String} scope
             * @param {Object} options
             */
            setScope: function (scope, options) {
                UserContext.set('catalogScope', scope, options);
            },

            /**
             * Get the current scope
             */
            getScope: function () {
                return UserContext.get('catalogScope');
            },

            /**
             * Initialize  the locale if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initLocale: function (event) {
                if (undefined === this.getLocale()) {
                    this.setLocale(event.localeCode, {silent: true});
                } else {
                    event.localeCode = this.getLocale();
                }
            },

            /**
             * Set the current locale
             *
             * @param {String} locale
             * @param {Object} options
             */
            setLocale: function (locale, options) {
                UserContext.set('catalogLocale', locale, options);
            },

            /**
             * Get the current locale
             */
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },

            /**
             * Post save actions
             */
            postSave: function () {
                FieldManager.fields = {};
                this.render();
            },

            /**
             * Switch to the given attribute
             *
             * @param {Event} event
             */
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                    .then(function (attributeGroups) {
                        this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);

                        var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            event.attribute
                        );
                        var needRendering = false;

                        if (!attributeGroup) {
                            return;
                        }

                        if (event.scope) {
                            this.setScope(event.scope, {silent: true});
                            needRendering = true;
                        }
                        if (event.locale) {
                            this.setLocale(event.locale, {silent: true});
                            needRendering = true;
                        }

                        var attributeGroupSelector = this.getExtension('attribute-group-selector');
                        if (attributeGroup !== attributeGroupSelector.getCurrent()) {
                            attributeGroupSelector.setCurrent(attributeGroup);
                            needRendering = true;
                        }

                        if (needRendering) {
                            this.render();
                        }

                        var displayedAttributes = FieldManager.getFields();

                        if (_.has(displayedAttributes, event.attribute)) {
                            displayedAttributes[event.attribute].setFocus();
                        }
                    }.bind(this));
            },

            /**
             * Toggle the comparison mode
             *
             * @param {Boolean} open
             */
            comparisonChange: function (open) {
                this.$el[open ? 'addClass' : 'removeClass']('comparison-mode');
                this.$el.find('.AknAttributeActions')[open ? 'addClass' : 'removeClass'](
                    'AknAttributeActions--comparisonMode'
                );
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});