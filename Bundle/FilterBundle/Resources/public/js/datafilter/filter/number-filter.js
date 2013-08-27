var Oro = Oro || {};
Oro.Filter = Oro.Filter || {};

/**
 * Number filter: formats value as a number
 *
 * @class   Oro.Filter.NumberFilter
 * @extends Oro.Filter.ChoiceFilter
 */
Oro.Filter.NumberFilter = Oro.Filter.ChoiceFilter.extend({
    /** @property {Oro.Filter.NumberFormatter} */
    formatter: new Oro.Filter.NumberFormatter(),

    /** @property {Object} */
    formatterOptions: {},

    /**
     * Initialize.
     *
     * @param {Object} options
     * @param {*} [options.formatter] Object with methods fromRaw and toRaw or a string name of formatter (e.g. "integer", "decimal")
     */
    initialize: function(options) {
        options = options || {};
        this.formatter = new Oro.Filter.NumberFormatter(this.formatterOptions);
        Oro.Filter.ChoiceFilter.prototype.initialize.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    _formatRawValue: function(value) {
        if (value.value === '') {
            value.value = undefined;
        } else {
            value.value = this.formatter.toRaw(String(value.value));
        }
        return value;
    },

    /**
     * @inheritDoc
     */
    _formatDisplayValue: function(value) {
        if (_.isNumber(value.value)) {
            value.value = this.formatter.fromRaw(value.value);
        }
        return value;
    }
});
