import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate, LocaleCode, getLabel, ChannelCode, Channel, ChannelReference} from '@akeneo-pim-community/shared';

type ChannelDropdownProps = {
  title?: string;
  readOnly?: boolean;
  channel: ChannelReference;
  uiLocale: LocaleCode;
  onChange: (newChannel: ChannelCode) => void;
  channels: Channel[];
};

const ChannelDropdown = ({channel, uiLocale, channels, ...rest}: ChannelDropdownProps) => {
  const translate = useTranslate();

  return (
    <SelectInput
      value={channel}
      clearable={false}
      placeholder={translate('pim_asset_manager.asset.mass_edit.select.channel')}
      emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
      openLabel={translate('pim_common.open')}
      {...rest}
    >
      {channels.map(currentChannel => (
        <SelectInput.Option key={currentChannel.code} value={currentChannel.code}>
          {getLabel(currentChannel.labels, uiLocale, currentChannel.code)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {ChannelDropdown};
