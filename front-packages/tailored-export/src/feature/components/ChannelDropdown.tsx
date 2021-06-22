import React, {ReactElement} from 'react';
import {Field, Helper, HelperProps, SelectInput} from 'akeneo-design-system';
import {
  Channel,
  ChannelCode,
  getLabel,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';

type ChannelDropdownProps = {
  value: ChannelCode;
  channels: Channel[];
  validationErrors: ValidationError[];
  onChange: (updatedValue: ChannelCode) => void;
  children?: ReactElement<HelperProps> | null | false;
};

const ChannelDropdown = ({value, children, channels, validationErrors, onChange}: ChannelDropdownProps) => {
  const translate = useTranslate();
  const userContext = useUserContext();

  return (
    <Field label={translate('pim_common.channel')}>
      <SelectInput
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={value}
        onChange={onChange}
      >
        {channels.map(channel => (
          <SelectInput.Option
            key={channel.code}
            title={getLabel(channel.labels, userContext.get('catalogLocale'), channel.code)}
            value={channel.code}
          >
            {getLabel(channel.labels, userContext.get('catalogLocale'), channel.code)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {children}
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {ChannelDropdown};
