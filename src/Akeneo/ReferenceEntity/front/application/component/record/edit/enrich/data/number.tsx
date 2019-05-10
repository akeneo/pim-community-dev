import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import NumberData, {create} from 'akeneoreferenceentity/domain/model/record/data/number';
import {ConcreteNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import Key from 'akeneoreferenceentity/tools/key';

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

  const onValueChange = (number: string) => {
    const newData = create(number);
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
        value={value.data.stringValue()}
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
