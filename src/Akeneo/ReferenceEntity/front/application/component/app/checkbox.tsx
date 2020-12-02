import React from 'react';
import {CheckIcon, pimTheme} from 'akeneo-design-system';

class InvalidArgumentError extends Error {}

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
      <CheckIcon size={18} color={pimTheme.color.white} className="AknCheckbox-tickPath" />
    </div>
  );
};

export default Checkbox;
