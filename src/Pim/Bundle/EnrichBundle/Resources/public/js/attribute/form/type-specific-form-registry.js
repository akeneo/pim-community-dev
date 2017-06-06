'use strict';

define(['module', 'underscore'], function (module, _) {
    return {
        formNames: {},

        initialize: function () {
            this.formNames = module.config().formNames;

            return this;
        },

        /**
         * Get the form name corresponding to the specified attribute type, or null.
         *
         * @param {String} attributeType
         * @param {String} mode
         *
         * @return {String}
         */
        getFormName: function (attributeType, mode) {
            return _.has(this.formNames, attributeType) ?
                this.formNames[attributeType][mode] :
                null;
        }
    };
});
