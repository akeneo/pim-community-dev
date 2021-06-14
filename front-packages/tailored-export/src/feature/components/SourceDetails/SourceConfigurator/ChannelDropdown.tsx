import {ChannelCode, getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Field, SelectInput} from 'akeneo-design-system';
import {useChannels} from '../../../hooks';
import React from 'react';

type ChannelDropdownProps = {
  value: ChannelCode;
  onChange: (updatedValue: ChannelCode) => void;
};

const ChannelDropdown = ({value, onChange}: ChannelDropdownProps) => {
  const translate = useTranslate();
  const channels = useChannels();
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
    </Field>
  );
};

export {ChannelDropdown};
