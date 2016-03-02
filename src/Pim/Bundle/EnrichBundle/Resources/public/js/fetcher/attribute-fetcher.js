'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function ($, _, BaseFetcher, Routing) {
    return BaseFetcher.extend({
        identifierPromise: null,

        /**
         * Return the identifier attribute
         *
         * @return {Promise}
         */
        getIdentifierAttribute: function () {
            if (null === this.identifierPromise) {
                return this.fetchByTypes([this.options.identifier_type])
                    .then(function (attributes) {
                        if (attributes.length > 0) {
                            this.identifierPromise = $.Deferred().resolve(attributes[0]);

                            return this.identifierPromise;
                        }

                        return $.Deferred()
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
            return this.getJSON(this.options.urls.list, { types: attributeTypes.join(',') })
                .then(_.identity)
                .promise();
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
});
