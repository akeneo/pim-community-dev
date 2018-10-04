import * as React from 'react';

class InvalidArgumentError extends Error {}

const Tick = ({className}: {className: string}) => (
  <svg width={16} height={16}>
    <path
      className={className}
      fill="none"
      stroke="#FFFFFF"
      strokeWidth={1}
      strokeLinejoin="round"
      strokeMiterlimit={10}
      d="M1.7 8l4.1 4 8-8"
    />
  </svg>
);

const Checkbox = ({
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
    throw new InvalidArgumentError(`A Checkbox element expect a onChange attribute if not readOnly`);
  }

  return (
    <div
      className={`AknCheckbox AknCheckbox--inline ${value ? 'AknCheckbox--checked' : ''} ${
        readOnly ? 'AknCheckbox--disabled' : ''
      }`}
      data-checked={value ? 'true' : 'false'}
      tabIndex={readOnly ? -1 : 0}
      id={id}
      role="checkbox"
      aria-checked={value ? 'true' : 'false'}
      onKeyPress={(event: React.KeyboardEvent<HTMLSpanElement>) => {
        if ([' '].includes(event.key) && !readOnly && onChange) onChange(!value);
        event.preventDefault();
      }}
      onClick={() => {
        if (!readOnly && onChange) onChange(!value);
      }}
    >
      <Tick className="AknCheckbox-tickPath" />
    </div>
  );
};

export default Checkbox;
