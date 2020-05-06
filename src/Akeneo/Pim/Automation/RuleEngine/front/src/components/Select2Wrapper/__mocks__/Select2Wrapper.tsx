import React, { ChangeEvent } from 'react';
import { Select2Props, Select2Wrapper as BaseWrapper } from '../Select2Wrapper';
import { Label } from '../../Labels';

type option = { id: number | string; text: string };

const Select2Wrapper: typeof BaseWrapper = ({
  data,
  hiddenLabel = false,
  id,
  label,
  onChange,
  value,
  multiple = false,
}: Select2Props) => {
  const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
    if (onChange) {
      onChange(event.target.value);
    }
  };

  let computedData: option[] = [];
  if (data) {
    computedData = data;
  }

  const defaultValue = value || computedData[0].id;

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <select
        id={id}
        data-testid={id}
        defaultValue={defaultValue}
        onChange={handleChange}
        multiple={multiple}>
        {computedData.map(({ id, text }) => (
          <option key={id} value={id}>
            {text}
          </option>
        ))}
      </select>
    </>
  );
};

export { Select2Wrapper };
