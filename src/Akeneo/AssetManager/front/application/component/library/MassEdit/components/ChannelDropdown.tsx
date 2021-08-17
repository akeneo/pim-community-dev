import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {getLabel} from 'pimui/js/i18n';
import {useTranslate} from '@akeneo-pim-community/shared';

type ChannelDropdownProps = {
  title?: string;
  readOnly?: boolean;
  channel: ChannelCode;
  uiLocale: LocaleCode;
  onChange: (newChannel: ChannelCode) => void;
  channels: Channel[];
};

const ChannelDropdown = ({channel, uiLocale, channels, ...rest}: ChannelDropdownProps) => {
  const translate = useTranslate();
  const currentChannel = channels.find(channelItem => channelItem.code === channel);

  if (undefined === currentChannel) {
    return null;
  }

  return (
    <SelectInput
      value={currentChannel.code}
      clearable={false}
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
