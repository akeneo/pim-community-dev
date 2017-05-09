webpackJsonp([7,8],{

/***/ 101:
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/attribute-fetcher.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! pim/base-fetcher */ 88), __webpack_require__(/*! routing */ 10)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, BaseFetcher, Routing) {
    return BaseFetcher.extend({
        identifierPromise: null,
        fetchByTypesPromises: [],

        /**
         * Return the identifier attribute
         *
         * @return {Promise}
         */
        getIdentifierAttribute: function () {
            if (null === this.identifierPromise) {
                this.identifierPromise = $.Deferred();

                return this.fetchByTypes([this.options.identifier_type])
                    .then(function (attributes) {
                        if (attributes.length > 0) {
                            this.identifierPromise.resolve(attributes[0]).promise();

                            return this.identifierPromise;
                        }

                        return this.identifierPromise
                            .reject()
                            .promise();
                    }.bind(this));
            }

            return this.identifierPromise;
        },

        /**
         * Fetch attributes by types
         *
         * @param {Array} attributeTypes
         *
         * @return {Promise}
         */
        fetchByTypes: function (attributeTypes) {
            var cacheKey = attributeTypes.sort().join('');

            if (!_.has(this.fetchByTypesPromises, cacheKey)) {
                this.fetchByTypesPromises[cacheKey] = this.getJSON(
                    this.options.urls.list,
                    {types: attributeTypes.join(',')}
                )
                .then(_.identity)
                .promise();
            }

            return this.fetchByTypesPromises[cacheKey];
        },

        /**
         * This method overrides the base method, to send a POST query instead of a GET query, because the request
         * URI can be too long.
         * TODO Should be deleted to set it back to GET.
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        },

        /**
         * {@inheritdoc}
         */
        clear: function () {
            BaseFetcher.prototype.clear.apply(this, arguments);

            this.identifierPromise = null;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 88:
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/base-fetcher.js ***!
  \*********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global console */


!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 2), __webpack_require__(/*! routing */ 10)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone, Routing) {
    return Backbone.Model.extend({
        entityListPromise: null,
        entityPromises: {},

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            this.entityListPromise = null;
            this.entityPromises    = {};
            this.options           = options || {};
        },

        /**
         * Fetch all elements of the collection
         *
         * @return {Promise}
         */
        fetchAll: function () {
            if (!this.entityListPromise) {
                if (!_.has(this.options.urls, 'list')) {
                    return $.Deferred().reject().promise();
                }

                this.entityListPromise = $.getJSON(
                    Routing.generate(this.options.urls.list)
                ).then(_.identity).promise();
            }

            return this.entityListPromise;
        },

        /**
         * Search elements of the collection
         *
         * @return {Promise}
         */
        search: function (searchOptions) {
            if (!_.has(this.options.urls, 'list')) {
                return $.Deferred().reject().promise();
            }

            return this.getJSON(this.options.urls.list, searchOptions).then(_.identity).promise();
        },

        /**
         * Fetch an element based on its identifier
         *
         * @param {string} identifier
         * @param {Object} options
         *
         * @return {Promise}
         */
        fetch: function (identifier, options) {
            options = options || {};

            if (!(identifier in this.entityPromises) || false === options.cached) {
                var deferred = $.Deferred();

                if (this.options.urls.get) {
                    $.getJSON(
                        Routing.generate(this.options.urls.get, _.extend({identifier: identifier}, options))
                    ).then(_.identity).done(function (entity) {
                        deferred.resolve(entity);
                    }).fail(function () {
                        console.error('Error during fetching: ', arguments);

                        return deferred.reject();
                    });
                } else {
                    this.fetchAll().done(function (entities) {
                        var entity = _.findWhere(entities, {code: identifier});
                        if (entity) {
                            deferred.resolve(entity);
                        } else {
                            deferred.reject();
                        }
                    });
                }

                this.entityPromises[identifier] = deferred.promise();
            }

            return this.entityPromises[identifier];
        },

        /**
         * Fetch all entities for the given identifiers
         *
         * @param {Array} identifiers
         *
         * @return {Promise}
         */
        fetchByIdentifiers: function (identifiers) {
            if (0 === identifiers.length) {
                return $.Deferred().resolve([]).promise();
            }

            var uncachedIdentifiers = _.difference(identifiers, _.keys(this.entityPromises));
            if (0 === uncachedIdentifiers.length) {
                return this.getObjects(_.pick(this.entityPromises, identifiers));
            }

            return $.when(
                    this.getJSON(this.options.urls.list, { identifiers: uncachedIdentifiers.join(',') })
                        .then(_.identity),
                    this.getIdentifierField()
                ).then(function (entities, identifierCode) {
                    _.each(entities, function (entity) {
                        this.entityPromises[entity[identifierCode]] = $.Deferred().resolve(entity).promise();
                    }.bind(this));

                    return this.getObjects(_.pick(this.entityPromises, identifiers));
                }.bind(this));
        },

        /**
         * Get the list of elements in JSON format.
         *
         * @param {string} url
         * @param {Object} parameters
         *
         * @returns {Promise}
         */
        getJSON: function (url, parameters) {
            return $.getJSON(Routing.generate(url, parameters));
        },

        /**
         * Get the identifier attribute of the collection
         *
         * @return {Promise}
         */
        getIdentifierField: function () {
            return $.Deferred().resolve('code');
        },

        /**
         * Clear cache of the fetcher
         *
         * @param {string|null} identifier
         */
        clear: function (identifier) {
            if (identifier) {
                delete this.entityPromises[identifier];
            } else {
                this.entityListPromise = null;
                this.entityPromises    = {};
            }
        },

        /**
         * Wait for promises to resolve and return the promises results wrapped in a Promise
         *
         * @param {Array|Object} promises
         *
         * @return {Promise}
         */
        getObjects: function (promises) {
            return $.when.apply($, _.toArray(promises)).then(function () {
                return 0 !== arguments.length ? _.toArray(arguments) : [];
            });
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});