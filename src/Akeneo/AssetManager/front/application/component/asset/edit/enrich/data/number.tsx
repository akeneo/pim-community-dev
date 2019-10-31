import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import NumberData, {create} from 'akeneoassetmanager/domain/model/asset/data/number';
import {ConcreteNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import Key from 'akeneoassetmanager/tools/key';
import {unformatNumber, formatNumberForUILocale} from 'akeneoassetmanager/tools/format-number';

const View = ({
  value,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: Value;
  onChange: (value: Value) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof NumberData && value.attribute instanceof ConcreteNumberAttribute)) {
    return null;
  }
  const valueToDisplay = formatNumberForUILocale(value.data.stringValue());

  const onValueChange = (number: string) => {
    const unformattedNumber = unformatNumber(number);
    const newData = create(unformattedNumber);
    if (newData.equals(value.data)) {
      return;
    }
    const newValue = value.setData(newData);

    onChange(newValue);
  };

  return (
    <React.Fragment>
      <input
        id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
        autoComplete="off"
        className={`AknTextField AknTextField--narrow AknTextField--light
          ${value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''}
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
