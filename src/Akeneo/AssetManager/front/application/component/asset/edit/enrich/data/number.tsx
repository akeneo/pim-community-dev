import React from 'react';
import {NumberInput} from 'akeneo-design-system';
import {useUserContext} from '@akeneo-pim-community/shared';
import {
  numberDataStringValue,
  numberDataFromString,
  areNumberDataEqual,
} from 'akeneoassetmanager/domain/model/asset/data/number';
import {unformatNumber, formatNumberForUILocale} from 'akeneoassetmanager/tools/format-number';
import {isNumberData} from 'akeneoassetmanager/domain/model/asset/data/number';
import {isNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';

const View = ({value, id, invalid, canEditData, onChange, onSubmit}: ViewGeneratorProps) => {
  const decimalSeparator = useUserContext().get('ui_locale_decimal_separator');

  if (!isNumberData(value.data) || !isNumberAttribute(value.attribute)) {
    return null;
  }

  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  const valueToDisplay = formatNumberForUILocale(numberDataStringValue(value.data), decimalSeparator);

  const onValueChange = (number: string) => {
    const unformattedNumber = unformatNumber(number, decimalSeparator);
    const newData = numberDataFromString(unformattedNumber);
    if (areNumberDataEqual(newData, value.data)) {
      return;
    }
    const newValue = setValueData(value, newData);

    onChange(newValue);
  };

  return (
    <NumberInput
      id={id}
      value={valueToDisplay}
      onChange={onValueChange}
      readOnly={!canEditData}
      invalid={invalid}
      onSubmit={onSubmit}
    />
  );
};

export const view = View;
