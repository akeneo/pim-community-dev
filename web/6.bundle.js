webpackJsonp([6,8],{

/***/ 105:
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/attribute-group-fetcher.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Attribute group fetcher
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
    __webpack_require__(/*! jquery */ 1),
    __webpack_require__(/*! pim/base-fetcher */ 91),
    __webpack_require__(/*! routing */ 10)
], __WEBPACK_AMD_DEFINE_RESULT__ = function (
    $,
    BaseFetcher,
    Routing
) {
    return BaseFetcher.extend({
        /**
         * Overrides base method, to send query using POST instead GET,
         * because the request URI can be too long.
         * TODO Should be deleted to set it back to GET.
         * SEE attribute fetcher
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 91:
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