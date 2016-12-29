'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/export/common/edit/field/select'
], function (
    _,
    __,
    FetcherRegistry,
    SelectField
) {
    return SelectField.extend({
        /**
         * {@inherit}
         */
        configure: function () {
            return $.when(
                FetcherRegistry.getFetcher('formats').fetchAll(),
                SelectField.prototype.configure.apply(this, arguments)
            ).then(function (formats) {
                this.config.options = formats.decimal_separators;
            }.bind(this));
        }
    });
});
