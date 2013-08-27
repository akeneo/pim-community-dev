var Oro = Oro || {};
Oro.Filter = Oro.Filter || {};

/**
 Just a convenient class for interested parties to subclass.

 The default Cell classes don't require the formatter to be a subclass of
 Formatter as long as the fromRaw(rawData) and toRaw(formattedData) methods
 are defined.

 @abstract
 @class Oro.Filter.Formatter
 @constructor
 */
Oro.Filter.Formatter = function () {};
_.extend(Oro.Filter.Formatter.prototype, {

    /**
     Takes a raw value from a model and returns a formatted string for display.

     @member Oro.Filter.Formatter
     @param {*} rawData
     @return {string}
     */
    fromRaw: function (rawData) {
        return rawData;
    },

    /**
     Takes a formatted string, usually from user input, and returns a
     appropriately typed value for persistence in the model.

     If the user input is invalid or unable to be converted to a raw value
     suitable for persistence in the model, toRaw must return `undefined`.

     @member Oro.Filter.Formatter
     @param {string} formattedData
     @return {*|undefined}
     */
    toRaw: function (formattedData) {
        return formattedData;
    }
});
