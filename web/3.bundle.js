webpackJsonp([3],{

/***/ 107:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/product-fetcher.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! module-config */ 16),
        __webpack_require__(/*! routing */ 10),
        __webpack_require__(/*! oro/mediator */ 14),
        __webpack_require__(/*! pim/cache-invalidator */ 91),
        __webpack_require__(/*! pim/product-manager */ 93)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        Backbone,
        module,
        Routing,
        mediator,
        CacheInvalidator,
        ProductManager
    ) {
        return Backbone.Model.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch an element based on its identifier
             *
             * @param {string} identifier
             *
             * @return {Promise}
             */
            fetch: function (identifier) {
                return $.getJSON(Routing.generate(this.options.urls.get, { id: identifier }))
                    .then(function (product) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(product);

                        return ProductManager.generateMissing(product);
                    }.bind(this))
                    .then(function (product) {
                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 91:
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/cache-invalidator.js ***!
  \***********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! module-config */ 16),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/form */ 94),
        __webpack_require__(/*! oro/mediator */ 14),
        __webpack_require__(/*! pim/fetcher-registry */ 8)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (module, _, BaseForm, mediator, FetcherRegistry) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                _.each(module.config().events, function (event) {
                    this.listenTo(mediator, event, this.checkStructureVersion);
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:cache:clear', this.clearCache);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Check if the given entity need e newer version of the cache
             *
             * @param {Object} entity
             */
            checkStructureVersion: function (entity) {
                if (entity.meta.structure_version !== this.getLocaleStructureVersion(entity.meta.model_type)) {
                    this.clearCache();
                }

                this.setLocaleStructureVersion(entity.meta.model_type, entity.meta.structure_version);
            },

            /**
             * Get the in locale storage structure version
             *
             * @param {string} modelType
             *
             * @return {int}
             */
            getLocaleStructureVersion: function (modelType) {
                return parseInt(sessionStorage.getItem('structure_version_' + modelType));
            },

            /**
             * Set the current locale structure version in locale storage
             *
             * @param {string} modelType
             * @param {int}    structureVersion
             */
            setLocaleStructureVersion: function (modelType, structureVersion) {
                sessionStorage.setItem('structure_version_' + modelType, structureVersion);
            },

            /**
             * Clear the cache for all fetchers
             */
            clearCache: function () {
                FetcherRegistry.clearAll();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 92:
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/manager/attribute-manager.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/fetcher-registry */ 8)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        FetcherRegistry
    ) {
        return {
            /**
             * Get the attributes of the given entity
             *
             * @param {Object} entity
             *
             * @return {Promise}
             */
            getAttributes: function (entity) {
                if (!entity.family) {
                    return $.Deferred().resolve(_.keys(entity.values));
                } else {
                    return FetcherRegistry.getFetcher('family')
                        .fetch(entity.family)
                        .then(function (family) {
                            return _.union(
                                _.keys(entity.values),
                                _.pluck(family.attributes, 'code')
                            );
                        });
                }
            },

            /**
             * Get all optional attributes available for a product
             *
             * @param {Object} product
             *
             * @return {Array}
             */
            getAvailableOptionalAttributes: function (product) {
                return $.when(
                    FetcherRegistry.getFetcher('attribute').fetchAll(),
                    this.getAttributes(product)
                ).then(function (attributes, productAttributes) {
                    var optionalAttributes = _.map(
                        _.difference(_.pluck(attributes, 'code'), productAttributes),
                        function (attributeCode) {
                            return _.findWhere(attributes, { code: attributeCode });
                        }
                    );

                    return optionalAttributes;
                });
            },

            /**
             * Check if an attribute is optional
             *
             * @param {Object} attribute
             * @param {Object} product
             *
             * @return {Promise}
             */
            isOptional: function (attribute, product) {
                var promise = new $.Deferred();

                if ('pim_catalog_identifier' === attribute.type) {
                    promise.resolve(false);
                } else if (undefined !== product.family && null !== product.family) {
                    promise = FetcherRegistry.getFetcher('family').fetch(product.family).then(function (family) {
                        return !_.contains(_.pluck(family.attributes, 'code'), attribute.code);
                    });
                } else {
                    promise.resolve(true);
                }

                return promise;
            },

            /**
             * Get the value in the given collection for the given locale and scope
             *
             * @param {Array}  values
             * @param {Object} attribute
             * @param {string} locale
             * @param {string} scope
             *
             * @return {Object}
             */
            getValue: function (values, attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return _.findWhere(values, { scope: scope, locale: locale });
            },

            /**
             * Get values for the given object
             *
             * @param {Object} object
             *
             * @return {Promise}
             */
            getValues: function (object) {
                return this.getAttributes(object).then(function (attributes) {
                    _.each(attributes, function (attributeCode) {
                        if (!_.has(object.values, attributeCode)) {
                            object.values[attributeCode] = [];
                        }
                    });

                    return object.values;
                });
            },

            /**
             * Generate a single value for the given attribute, scope and locale
             *
             * @param {Object} attribute
             * @param {string} locale
             * @param {string} scope
             *
             * @return {Object}
             */
            generateValue: function (attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return {
                    'locale': locale,
                    'scope':  scope,
                    'data':   attribute.empty_value
                };
            },

            /**
             * Generate all missing values for an attribute
             *
             * @param {Array}  values
             * @param {Object} attribute
             * @param {Array}  locales
             * @param {Array}  channels
             * @param {Array}  currencies
             *
             * @return {Array}
             */
            generateMissingValues: function (values, attribute, locales, channels, currencies) {
                _.each(locales, function (locale) {
                    _.each(channels, function (channel) {
                        var newValue = this.getValue(
                            values,
                            attribute,
                            locale.code,
                            channel.code
                        );

                        if (!newValue) {
                            newValue = this.generateValue(attribute, locale.code, channel.code);
                            values.push(newValue);
                        }

                        if ('pim_catalog_price_collection' === attribute.type) {
                            newValue.data = this.generateMissingPrices(newValue.data, currencies);
                        }
                    }.bind(this));
                }.bind(this));

                return values;
            },

            /**
             * Generate missing prices in the given collection for the given currencies
             *
             * @param {Array} prices
             * @param {Array} currencies
             *
             * @return {Array}
             */
            generateMissingPrices: function (prices, currencies) {
                var generatedPrices = [];
                _.each(currencies, function (currency) {
                    var price = _.findWhere(prices, { currency: currency.code });

                    if (!price) {
                        price = { amount: null, currency: currency.code };
                    }

                    generatedPrices.push(price);
                });

                return _.sortBy(generatedPrices, 'currency');
            },

            /**
             * Generate missing product associations
             *
             * @param {Array} values
             *
             * @return {Array}
             */
            generateMissingAssociations: function (values) {
                values.products = _.result(values, 'products', []).sort();
                values.groups = _.result(values, 'groups', []).sort();

                return values;
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 93:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/manager/product-manager.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! module-config */ 16),
        __webpack_require__(/*! oro/mediator */ 14),
        __webpack_require__(/*! routing */ 10),
        __webpack_require__(/*! pim/attribute-manager */ 92),
        __webpack_require__(/*! pim/fetcher-registry */ 8)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        module,
        mediator,
        Routing,
        AttributeManager,
        FetcherRegistry
    ) {
        return {
            productValues: null,
            doGenerateMissing: function (product) {
                return AttributeManager.getAttributes(product)
                    .then(function (productAttributeCodes) {
                        return $.when(
                            FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(productAttributeCodes),
                            FetcherRegistry.getFetcher('locale').fetchActivated(),
                            FetcherRegistry.getFetcher('channel').fetchAll(),
                            FetcherRegistry.getFetcher('currency').fetchAll(),
                            FetcherRegistry.getFetcher('association-type').fetchAll()
                        );
                    })
                    .then(function (attributes, locales, channels, currencies, associationTypes) {
                        var oldValues = _.isArray(product.values) && 0 === product.values.length ? {} : product.values;
                        var newValues = {};

                        _.each(attributes, function (attribute) {
                            newValues[attribute.code] = AttributeManager.generateMissingValues(
                                _.has(oldValues, attribute.code) ? oldValues[attribute.code] : [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        });

                        var associations = {};
                        _.each(associationTypes, function (assocType) {
                            associations[assocType.code] = AttributeManager.generateMissingAssociations(
                                _.has(product.associations, assocType.code) ? product.associations[assocType.code] : {}
                            );
                        });

                        product.values       = newValues;
                        product.associations = associations;

                        return product;
                    });
            },
            generateMissing: function (product) {
                return this.doGenerateMissing(product);
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 94:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/form.js ***!
  \*************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Form main class
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
        __webpack_require__(/*! oro/mediator */ 14)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        mediator
    ) {
        return Backbone.View.extend({
            code: 'form',
            parent: null,
            preUpdateEventName: 'pim_enrich:form:entity:pre_update',
            postUpdateEventName: 'pim_enrich:form:entity:post_update',

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.extensions = {};
                this.zones      = {};
                this.targetZone = '';
                this.configured = false;
            },

            /**
             * Configure the extension and its child extensions
             *
             * @return {Promise}
             */
            configure: function () {
                if (null === this.parent) {
                    this.model = new Backbone.Model();
                }

                var extensionPromises = _.map(this.extensions, function (extension) {
                    return extension.configure();
                });

                return $.when.apply($, extensionPromises).then(function () {
                    this.configured = true;
                }.bind(this));
            },

            /**
             * Add a child extension to this extension
             *
             * @param {string} code      Extension's code
             * @param {Object} extension Backbone module of the extension
             * @param {string} zone      Targeted zone
             * @param {int} position     The position of the extension
             */
            addExtension: function (code, extension, zone, position) {
                extension.setParent(this);

                extension.code       = code;
                extension.targetZone = zone;
                extension.position   = position;

                this.extensions[code] = extension;
            },

            /**
             * Get a child extension (the first extension matching the given code or ends with the given code)
             *
             * @param {string} code
             *
             * @return {Object}
             */
            getExtension: function (code) {
                return this.extensions[_.findKey(this.extensions, function (extension) {
                    var expectedPosition = extension.code.length - code.length;

                    return expectedPosition >= 0 && expectedPosition === extension.code.indexOf(code, expectedPosition);
                })];
            },

            /**
             * Set the parent of this extension
             *
             * @param {Object} parent
             */
            setParent: function (parent) {
                this.parent = parent;

                return this;
            },

            /**
             * Get the parent of the extension
             *
             * @return {Object}
             */
            getParent: function () {
                return this.parent;
            },

            /**
             * Get the root extension
             *
             * @return {Object}
             */
            getRoot: function () {
                var rootView = this;
                var parent = this.getParent();
                while (parent) {
                    rootView = parent;
                    parent = parent.getParent();
                }

                return rootView;
            },

            /**
             * Set data in the root model
             *
             * @param {Object} data
             * @param {Object} options If silent is set to true, don't fire events
             *                         pim_enrich:form:entity:pre_update and pim_enrich:form:entity:post_update
             */
            setData: function (data, options) {
                options = options || {};

                if (!options.silent) {
                    this.getRoot().trigger(this.preUpdateEventName, data);
                }

                this.getRoot().model.set(data, options);

                if (!options.silent) {
                    this.getRoot().trigger(this.postUpdateEventName, data);
                }

                return this;
            },

            /**
             * Get the form raw data (vanilla javascript object)
             *
             * @return {Object}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON();
            },

            /**
             * Get the form data (backbone model)
             *
             * @return {Object}
             */
            getFormModel: function () {
                return this.getRoot().model;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                return this.renderExtensions();
            },

            /**
             * Render the child extensions
             *
             * @return {Object}
             */
            renderExtensions: function () {
                // If the view is no longer attached to the DOM, don't render the extensions
                if (undefined === this.el) {
                    return this;
                }

                this.initializeDropZones();

                _.each(this.extensions, function (extension) {
                    this.renderExtension(extension);
                }.bind(this));

                return this;
            },

            /**
             * Render a single extension
             *
             * @param {Object} extension
             */
            renderExtension: function (extension) {
                this.getZone(extension.targetZone).appendChild(extension.el);

                extension.render();
            },

            /**
             * Initialize dropzone cache
             */
            initializeDropZones: function () {
                this.zones = _.indexBy(this.$('[data-drop-zone]'), function (zone) {
                    return zone.dataset.dropZone;
                });

                this.zones.self = this.el;
            },

            /**
             * Get the drop zone for the given code
             *
             * @param {string} code
             *
             * @return {jQueryElement}
             */
            getZone: function (code) {
                if (!(code in this.zones)) {
                    this.zones[code] = this.$('[data-drop-zone="' + code + '"]')[0];
                }

                if (!this.zones[code]) {
                    throw new Error('Zone "' + code + '" does not exist');
                }

                return this.zones[code];
            },

            /**
             * Trigger event on each child extensions and their childs
             */
            triggerExtensions: function () {
                var options = _.toArray(arguments);

                _.each(this.extensions, function (extension) {
                    extension.trigger.apply(extension, options);
                    extension.triggerExtensions.apply(extension, options);
                });
            },

            /**
             * Listen on child extensions and their childs events
             *
             * @param {string}   code
             * @param {Function} callback
             */
            onExtensions: function (code, callback) {
                _.each(this.extensions, function (extension) {
                    this.listenTo(extension, code, callback);
                }.bind(this));
            },

            /**
             * Get the root form code
             *
             * @return {string}
             */
            getFormCode: function () {
                return this.getRoot().code;
            },

            /**
             * Listen to given mediator events to trigger them locally (in the local root).
             * This way, extensions attached to this form don't have to listen "globally" on the mediator.
             *
             * @param {Array} mediator events to forward:
             *                [ {'mediator:event:name': 'this:event:name'}, {...} ]
             */
            forwardMediatorEvents: function (events) {
                _.map(events, function (localEvent, mediatorEvent) {
                    this.listenTo(mediator, mediatorEvent, function (data) {
                        this.trigger(localEvent, data);
                    });
                }.bind(this));
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});