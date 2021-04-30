define(['oro/datafilter/choice-filter'], function(ChoiceFilter) {
    'use strict';

    /**
     * Number filter: formats value as a number
     *
     * @export  oro/datafilter/number-filter
     * @class   oro.datafilter.NumberFilter
     * @extends oro.datafilter.ChoiceFilter
     */
    return ChoiceFilter.extend({
        /**
         * {@inheritdoc}
         */
        _onClickUpdateCriteria: function() {
            const numberValue = Number(this._getInputValue(this.criteriaValueSelectors.value));

            if (isNaN(numberValue)) {
                this._setInputValue(this.criteriaValueSelectors.value, '');
                this._focusCriteria();
            } else {
                this._hideCriteria();
                this.setValue(this._formatRawValue(this._readDOMValue()));
            }
        },
    });
});
