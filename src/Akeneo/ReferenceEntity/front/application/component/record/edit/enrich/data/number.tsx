import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import NumberData, {create} from 'akeneoreferenceentity/domain/model/record/data/number';
import {ConcreteNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import Key from 'akeneoreferenceentity/tools/key';

const UserContext = require('pim/user-context');

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
        id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
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
        data-raw-number={value.data.stringValue()}
      />
    </React.Fragment>
  );
};

const unformatNumber = (numberToUnformat: string): string =>
  numberToUnformat.replace(' ', '').replace(decimalSeparator(), '.');

const formatNumberForUILocale = (number: any): string => number.replace(/\./g, decimalSeparator());
const decimalSeparator = (): string => UserContext.get('ui-locale-decimal-separator');

export const view = View;
