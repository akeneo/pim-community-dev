import * as React from 'react';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {
  numberDataStringValue,
  numberDataFromString,
  areNumberDataEqual,
} from 'akeneoassetmanager/domain/model/asset/data/number';
import {Key} from 'akeneo-design-system';
import {unformatNumber, formatNumberForUILocale} from 'akeneoassetmanager/tools/format-number';
import {isNumberData} from 'akeneoassetmanager/domain/model/asset/data/number';
import {isNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';

const View = ({
  value,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: EditionValue;
  onChange: (value: EditionValue) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!isNumberData(value.data) || !isNumberAttribute(value.attribute)) {
    return null;
  }
  const valueToDisplay = formatNumberForUILocale(numberDataStringValue(value.data));

  const onValueChange = (number: string) => {
    const unformattedNumber = unformatNumber(number);
    const newData = numberDataFromString(unformattedNumber);
    if (areNumberDataEqual(newData, value.data)) {
      return;
    }
    const newValue = setValueData(value, newData);

    onChange(newValue);
  };

  return (
    <React.Fragment>
      <input
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        autoComplete="off"
        className={`AknTextField AknTextField--narrow AknTextField--light
          ${value.attribute.value_per_locale ? 'AknTextField--localizable' : ''}
          ${!canEditData ? 'AknTextField--disabled' : ''}`}
        value={valueToDisplay}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          onValueChange(event.currentTarget.value);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Enter === event.key) onSubmit();
        }}
        disabled={!canEditData}
        readOnly={!canEditData}
      />
    </React.Fragment>
  );
};

export const view = View;
