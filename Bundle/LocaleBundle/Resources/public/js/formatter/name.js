/* global define */
define(['underscore', 'oro/locale-settings'],
function(_, localeSettings) {
    'use strict';

    /**
     * Name formatter
     *
     * @export  oro/formatter/name
     * @class   oro.NameFormatter
     */
    return {
        format: function(person, format) {
            format = format || localeSettings.nameFormat;

        }
    }
});