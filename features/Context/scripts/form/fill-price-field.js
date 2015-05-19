function (currencySymbol, attributeLabel, value) {
    var $field = $('.attribute-field.currency label:contains("' + attributeLabel + '")').parent();
    var $input;
    if ($field.length) {
        var fieldIndex;
        $field.find('.currency-label').each(function (index, subLabel) {
            if ($(subLabel).text() === currencySymbol) {
                fieldIndex = index;
            }
        });
        $input = $field.find('.controls input.input-small').eq(fieldIndex);
    } else {
        $field = $('.field-container[data-attribute="' + attributeLabel.toLowerCase() + '"]');
        switch (currencySymbol) {
            case '$':
                $input = $field.find('input[data-currency="USD"]');
                break;
            case '€':
                $input = $field.find('input[data-currency="EUR"]');
                break;
            default:
                break;
        }
    }

    if ($input && $input.length && $input.is(':visible')) {
        $input.val(value).trigger('change');
        return true;
    }

    return 'No field found for ' + attributeLabel + ' in currency ' + currencySymbol;
}
