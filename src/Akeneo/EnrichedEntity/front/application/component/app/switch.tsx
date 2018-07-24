import * as React from 'react';

const Switch = ({value, onChange, id = ''}: {value: boolean; id: string; onChange: (value: boolean) => void}) => {
  return (
    <label
      className="AknSwitch"
      tabIndex={0}
      role="checkbox"
      aria-checked={value ? 'true' : 'false'}
      onKeyPress={event => {
        if ([' '].includes(event.key)) onChange(!value);
      }}
    >
      <input
        id={id}
        type="checkbox"
        className="AknSwitch-input"
        checked={value}
        onChange={(event: any) => {
          onChange(event.target.checked);
        }}
      />
      <span className="AknSwitch-slider" />
    </label>
  );
};

export default Switch;
