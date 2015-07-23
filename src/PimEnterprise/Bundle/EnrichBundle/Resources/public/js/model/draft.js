'use strict';
/**
 * Draft model class
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/attribute-manager'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        AttributeManager
    ) {
        return Backbone.Model.extend({
            urls: {},

            setUrl: function (key, url) {
                this.urls[key] = url;
            },

            /**
             * Return true if status is set
             *
             * @returns {boolean}
             */
            hasStatus: function () {
                return this.has('status');
            },

            /**
             * Return true is the draft status is ready, false otherwise (in progress typically)
             *
             * @returns {boolean}
             */
            isReady: function () {
                return this.hasStatus() && 0 !== this.get('status');
            },

            /**
             * Apply draft values on product values
             * productData is modified by reference
             *
             * @param {Object} productData
             *
             * @returns {Promise}
             */
            applyToProduct: function (productData) {
                var changes = this.get('changes');

                if (changes && changes.values) {
                    _.each(changes.values, function (draftValues, attributeCode) {
                        _.each(draftValues, function (draftValue) {
                            var productValue = _.findWhere(
                                productData.values[attributeCode],
                                {locale: draftValue.locale, scope: draftValue.scope}
                            );

                            productValue.data = draftValue.data;
                        });
                    });
                }
            },

            /**
             * Check if the specified field's value has been modified in the draft
             * If locale and/or scope is omitted the field's locale and/or scope is used by default
             *
             * @param {Object} field
             * @param {string} locale
             * @param {string} scope
             *
             * @returns {boolean}
             */
            isValueChanged: function (field, locale, scope) {
                locale = locale || field.context.locale;
                scope = scope || field.context.scope;
                var attribute = field.attribute;
                var changes = this.get('changes');

                if (!changes || !changes.values || !_.has(changes.values, attribute.code)) {
                    return false;
                }

                return !_.isUndefined(AttributeManager.getValue(
                    changes.values[attribute.code],
                    attribute,
                    locale,
                    scope
                ));
            },

            /**
             * Call the backend action to mark this draft as ready for approval
             *
             * @returns {Promise}
             */
            sendForApproval: function () {
                return $.post(
                    Routing.generate(
                        this.urls.ready,
                        {id: this.id}
                    )
                ).promise();
            }
        });
    }
);
