import * as React from 'react';

class InvalidArgumentError extends Error {}

const Switch = ({
  value,
  onChange,
  id = '',
  readOnly = false,
}: {
  value: boolean;
  id: string;
  onChange?: (value: boolean) => void;
  readOnly?: boolean;
}) => {
  if (undefined === onChange && false === readOnly) {
    throw new InvalidArgumentError(`A Switch element expect a onChange attribute if not readOnly`);
  }

  return (
    <label
      className={`AknSwitch ${readOnly ? 'AknSwitch--disabled' : ''}`}
      tabIndex={0}
      role="checkbox"
      aria-checked={value ? 'true' : 'false'}
      onKeyPress={event => {
        if ([' '].includes(event.key) && !readOnly && onChange) onChange(!value);
      }}
    >
      <input
        id={id}
        type="checkbox"
        className="AknSwitch-input"
        checked={value}
        onChange={(event: any) => {
          if (!readOnly && onChange) onChange(event.target.checked);
        }}
      />
      <span className="AknSwitch-slider" />
    </label>
  );
};

export default Switch;
