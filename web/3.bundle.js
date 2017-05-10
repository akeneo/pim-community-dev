webpackJsonp([3],{

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

/***/ 210:
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/tab/attributes/attribute-group-selector.html ***!
  \**********************************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<li class=\"AknVerticalNavtab-item AknVerticalNavtab-header header\"></li>\n<% _.each(elements, function (element) { %>\n    <li class=\"AknVerticalNavtab-item <%- current === element.code ? 'active' : '' %>\" data-element=\"<%- element.code %>\">\n        <a class=\"AknVerticalNavtab-link <%- current === element.code ? 'AknVerticalNavtab-link--active' : '' %>\">\n            <span class=\"group-label\"><%- i18n.getLabel(element.labels, locale, element.code) %></span>\n            <span class=\"badge-elements-container\">\n                <span\n                    class=\"AknBadge AknBadge--round AknBadge--highlight <%- !_.contains(toFillAttributeGroups, element.code) ? 'AknBadge--hidden' : '' %>\"\n                ></span>\n                <% _.each(badges[element.code], function(badge, type) { %>\n                    <span class=\"AknBadge AknBadge--<%- type %> <%- type %>-badge label\"><%- badge %></span>\n                <% }) %>\n            </span>\n        </a>\n    </li>\n<% }); %>\n"

/***/ }),

/***/ 219:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/attributes/attribute-group-selector.js ***!
  \************************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Attribute group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        !(function webpackMissingModule() { var e = new Error("Cannot find module \"pim/form/common/group-selector\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()),
        __webpack_require__(/*! pim/attribute-group-manager */ 152),
        __webpack_require__(/*! text-loader!pim/template/form/tab/attribute/attribute-group-selector */ 210),
        __webpack_require__(/*! pim/user-context */ 7),
        __webpack_require__(/*! pim/i18n */ 18),
        __webpack_require__(/*! pim/provider/to-fill-field-provider */ 247)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, GroupSelectorForm, AttributeGroupManager, template, UserContext, i18n, toFillFieldProvider) {
        return GroupSelectorForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.onValidationError);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.onPostFetch);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogScope', this.render);

                return GroupSelectorForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Triggered on validation error
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.removeBadges();

                var object = event.sentData;
                var valuesErrors = _.uniq(event.response.values, function (error) {
                    return JSON.stringify(error);
                });

                if (valuesErrors) {
                    AttributeGroupManager.getAttributeGroupsForObject(object)
                        .then(function (attributeGroups) {
                            var globalErrors = [];
                            _.each(valuesErrors, function (error) {
                                if (error.global) {
                                    globalErrors.push(error);
                                }

                                var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                    attributeGroups,
                                    error.attribute
                                );
                                this.addToBadge(attributeGroup, 'invalid');
                            }.bind(this));

                            // Don't force attributes tab if only global errors
                            if (!_.isEmpty(valuesErrors) && valuesErrors.length > globalErrors.length) {
                                this.getRoot().trigger('pim_enrich:form:show_attribute', _.first(valuesErrors));
                            }
                        }.bind(this));
                }
            },

            /**
             * Triggered on post fetch
             */
            onPostFetch: function () {
                this.removeBadges();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                $.when(
                    toFillFieldProvider.getFields(this.getRoot(), this.getFormData()),
                    AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                ).then(function (attributes, attributeGroups) {
                    var toFillAttributeGroups = _.uniq(_.map(attributes, function (attribute) {
                        return AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            attribute
                        );
                    }));

                    this.$el.html(this.template({
                        current: this.getCurrent(),
                        elements: this.getElements(),
                        badges: this.badges,
                        locale: UserContext.get('catalogLocale'),
                        toFillAttributeGroups: toFillAttributeGroups,
                        i18n: i18n
                    }));

                    this.delegateEvents();
                }.bind(this));

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 247:
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/provider/to-fill-field-provider.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Attribute group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! pim/attribute-manager */ 42),
        __webpack_require__(/*! pim/fetcher-registry */ 6)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, mediator, attributeManager, fetcherRegistry) {
        return {
            /**
             * Get list of fields that need to be filled to complete the product
             *
             * @param {object} root
             * @param {object} product
             *
             * @return {promise}
             */
            getFields: function (root, product) {
                var filterPromises = [];
                root.trigger(
                    'pim_enrich:form:field:to-fill-filter',
                    {'filters': filterPromises}
                );

                return $.when.apply($, filterPromises).then(function () {
                    return arguments;
                }).then(function (filters) {
                    return attributeManager.getAttributes(product)
                        .then(function (attributeCodes) {
                            return fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes);
                        })
                        .then(function (attributesToFilter) {
                            var filteredAttributes = _.reduce(filters, function (attributes, filter) {
                                return filter(attributes);
                            }, attributesToFilter);

                            return _.map(filteredAttributes, function (attribute) {
                                return attribute.code;
                            });
                        });
                });
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});