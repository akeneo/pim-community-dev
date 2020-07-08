import * as React from 'react';
import { Label } from '../Labels';
import { useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  id?: string;
  label?: string;
  hiddenLabel?: boolean;
  value: boolean;
  onChange?: (value: boolean) => void;
  readOnly?: boolean;
};

const InputBoolean: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  value,
  onChange,
  readOnly = false,
  children,
}) => {
  const translate = useTranslate();
  const [isChecked, setIsChecked] = React.useState<boolean>(value);

  return (
    <>
      {!children && label && (
        <Label
          className='AknFieldContainer-label control-label'
          hiddenLabel={hiddenLabel}
          htmlFor={id}
          label={label}
        />
      )}
      {children && children}
      <label
        className={`AknSwitch ${readOnly ? 'AknSwitch--disabled' : ''}`}
        role='checkbox'
        aria-checked={value ? 'true' : 'false'}
        onKeyPress={event => {
          setIsChecked(!isChecked);
          if ([' '].includes(event.key) && !readOnly && onChange) {
            onChange(!value);
          }
        }}>
        <input
          data-testid={id}
          type='checkbox'
          className='AknSwitch-input'
          defaultChecked={value}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            setIsChecked(!isChecked);
            if (undefined === event.target || readOnly) {
              return;
            }

            if (onChange) {
              onChange(event.target.checked);
            }
          }}
        />
        <span className='AknSwitch-slider' />
        <span className='AknSwitch-text'>
          {translate(isChecked ? 'pim_common.yes' : 'pim_common.no')}
        </span>
      </label>
    </>
  );
};

export default InputBoolean;
