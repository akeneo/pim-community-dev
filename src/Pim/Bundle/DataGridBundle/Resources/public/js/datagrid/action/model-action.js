/* global define */
define(['underscore', 'oro/datagrid/abstract-action'],
function(_, AbstractAction) {
    'use strict';

    /**
     * Basic model action class.
     *
     * @export  oro/datagrid/model-action
     * @class   oro.datagrid.ModelAction
     * @extends oro.datagrid.AbstractAction
     */
    return AbstractAction.extend({
        /** @property {Backbone.Model} */
        model: null,

        /** @property {String} */
        link: undefined,

        /** @property {Boolean} */
        backUrl: false,

        /** @property {String} */
        backUrlParameter: 'back',

        /**
         * Initialize view
         *
         * @param {Object} options
         * @param {Backbone.Model} options.model Optional parameter
         * @throws {TypeError} If model is undefined
         */
        initialize: function(options) {
            options = options || {};

            if (!options.model) {
                throw new TypeError("'model' is required");
            }
            this.model = options.model;

            if (_.has(options, 'backUrl')) {
                this.backUrl = options.backUrl;
            }

            if (_.has(options, 'backUrlParameter')) {
                this.backUrlParameter = options.backUrlParameter;
            }

            AbstractAction.prototype.initialize.apply(this, arguments);
        },

        /**
         * Get action link
         *
         * @return {String}
         * @throws {TypeError} If route is undefined
         */
        getLink: function() {
            if (!this.link) {
                throw new TypeError("'link' is required");
            }

            var result;

            if (this.model.has(this.link)) {
                result = this.model.get(this.link);
            } else {
                result = this.link;
            }

            if (this.backUrl) {
                var backUrl = _.isBoolean(this.backUrl) ? window.location.href : this.backUrl;
                backUrl = encodeURIComponent(backUrl);
                result = this.addUrlParameter(result, this.backUrlParameter, backUrl);
            }

            return result;
        },

        /**
         * Add parameter to URL
         *
         * @param {String} url
         * @param {String} parameterName
         * @param {String} parameterValue
         * @return {String}
         * @protected
         */
        addUrlParameter: function(url, parameterName, parameterValue) {
            var urlhash, sourceUrl, replaceDuplicates = true;
            if(url.indexOf('#') > 0){
                var cl = url.indexOf('#');
                urlhash = url.substring(url.indexOf('#'),url.length);
            } else {
                urlhash = '';
                cl = url.length;
            }
            sourceUrl = url.substring(0,cl);

            var urlParts = sourceUrl.split("?");
            var newQueryString = "";

            if (urlParts.length > 1) {
                var parameters = urlParts[1].split("&");
                for (var i=0; (i < parameters.length); i++)
                {
                    var parameterParts = parameters[i].split("=");
                    if (!(replaceDuplicates && parameterParts[0] == parameterName))
                    {
                        if (newQueryString == "")
                            newQueryString = "?";
                        else
                            newQueryString += "&";
                        newQueryString += parameterParts[0] + "=" + (parameterParts[1] ? parameterParts[1] : '');
                    }
                }
            }
            if (newQueryString == "") {
                newQueryString = "?";
            }
            if (newQueryString !== "" && newQueryString != '?') {
                newQueryString += "&";
            }
            newQueryString += parameterName + "=" + (parameterValue ? parameterValue : '');
            return urlParts[0] + newQueryString + urlhash;
        }
    });
});
